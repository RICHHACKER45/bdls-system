import React, { useState, useEffect, useMemo } from 'react';
import { Head, useForm, usePage, Link, router } from '@inertiajs/react';
import {
    BarChart,
    Bar,
    XAxis,
    YAxis,
    CartesianGrid,
    Tooltip,
    Legend,
    PieChart,
    Pie,
    Cell,
    ResponsiveContainer,
} from 'recharts';
import AdminLayout from '@/Layouts/AdminLayout';
import axios from 'axios';

const Pagination = ({ links }) => {
    if (!links || links.length <= 3) return null;
    return (
        <div className="mt-6 flex flex-wrap items-center justify-center gap-2">
            {links.map((link, i) => {
                const label = link.label
                    .replace('&laquo; Previous', 'Prev')
                    .replace('Next &raquo;', 'Next');
                return (
                    <Link
                        key={i}
                        href={link.url || '#'}
                        preserveScroll
                        preserveState
                        className={`flex min-w-[32px] items-center justify-center rounded-lg border px-3 py-2 text-xs font-bold transition-all ${
                            link.active
                                ? 'border-slate-900 bg-slate-900 text-white shadow-md'
                                : 'border-slate-200 bg-white text-slate-700 hover:border-slate-400 hover:bg-slate-50'
                        } ${!link.url ? 'pointer-events-none opacity-40' : ''}`}
                        dangerouslySetInnerHTML={{ __html: label }}
                    />
                );
            })}
        </div>
    );
};

// THE FIX: Exact Age Computation Helper (React-side) para maiwasan ang maling math (e.g. leap years, exact month/day precision)
const calculateAge = (dobString) => {
    if (!dobString) return 0;
    const dob = new Date(dobString);
    const diff_ms = Date.now() - dob.getTime();
    const age_dt = new Date(diff_ms);
    return Math.abs(age_dt.getUTCFullYear() - 1970);
};

export default function AdminDashboard() {
    // Idagdag ito malapit sa ibang useEffect sa Admin-Dashboard.jsx
    useEffect(() => {
        if (window.Echo) {
            // Makikinig ang Admin Portal sa public channel
            // THE FIX: Secure Private Channel
            window.Echo.private('admin-dashboard').listen('AdminDashboardUpdated', (e) => {
                // SILENT REFRESH: Kukuha ng bagong data ang Inertia nang walang screen refresh o loading UI!
                router.reload({
                    only: ['activeQueue', 'receivedQueue', 'auditLogs', 'notificationLogs'],
                    preserveScroll: true,
                    preserveState: true, // <-- ITO ANG MAGIC: Hindi mawawala ang tinatype o nakabukas na modal ng Admin
                });
            });
        }

        // Cleanup para hindi magdoble ang listener kapag umalis sa page
        return () => {
            if (window.Echo) window.Echo.leaveChannel('admin-dashboard');
        };
    }, []);
    const {
        activeQueue = { data: [], links: [] },
        receivedQueue = { data: [], links: [] },
        documents = [],
        auditLogs = { data: [], links: [] },
        analyticsSummary = {},
        censusRecords = { data: [], links: [] },
        residentAccounts = { data: [], links: [] },
        pendingUsers = [],
        filters = {},
        auth,
        flash = {},
        errors = {},
    } = usePage().props;

    const [activeTab, setActiveTab] = useState(flash?.active_tab || 'queue');

    useEffect(() => {
        if (flash?.active_tab) {
            setActiveTab(flash.active_tab);
        }
    }, [flash?.active_tab]);

    const [queueSubTab, setQueueSubTab] = useState('queue-active');
    const [analyticsSubTab, setAnalyticsSubTab] = useState('requests');
    const [accountsSubTab, setAccountsSubTab] = useState('list');
    const [residentSearch, setResidentSearch] = useState('');
    const [accountSearch, setAccountSearch] = useState('');
    const [selectedAccount, setSelectedAccount] = useState(null);
    const [suspendModalOpen, setSuspendModalOpen] = useState(false);
    const [deleteAccountModalOpen, setDeleteAccountModalOpen] = useState(false);
    const [residentModalOpen, setResidentModalOpen] = useState(false);
    const [importModalOpen, setImportModalOpen] = useState(false);

    // ADD STATE MANAGERS
    const [receiptModalOpen, setReceiptModalOpen] = useState(false);
    const [selectedReceiptPath, setSelectedReceiptPath] = useState('');
    const [manualVerifyConfirmOpen, setManualVerifyConfirmOpen] = useState(false);

    // ADD ACTION HANDLERS
    const triggerManualVerify = (account) => {
        setSelectedAccount(account);
        setManualVerifyConfirmOpen(true);
    };

    const submitManualVerify = () => {
        if (!selectedAccount) return;
        router.post(
            route('admin.accounts.manual_verify', selectedAccount.id),
            {},
            {
                preserveScroll: true,
                onSuccess: () => {
                    setManualVerifyConfirmOpen(false);
                    setSelectedAccount(null);
                },
            }
        );
    };

    const submitSuspend = () => {
        if (!selectedAccount) return;
        router.post(
            route('admin.accounts.suspend', selectedAccount.id),
            {},
            {
                preserveScroll: true,
                onSuccess: () => {
                    setSuspendModalOpen(false);
                    setSelectedAccount(null);
                },
            }
        );
    };

    const submitDelete = () => {
        if (!selectedAccount) return;
        router.delete(route('admin.accounts.destroy', selectedAccount.id), {
            preserveScroll: true,
            onSuccess: () => {
                setDeleteAccountModalOpen(false);
                setSelectedAccount(null);
            },
        });
    };

    const submitPendingManualVerify = (user) => {
        if (window.confirm(`Sigurado ka bang gusto mong manu-manong i-verify ang registration ni ${user.full_name}?`)) {
            router.post(route('admin.users.manual-verify', user.id), {}, { preserveScroll: true });
        }
    };

    const confirmRejectRegistration = (user) => {
        if (window.confirm(`Sigurado ka bang gusto mong i-reject at burahin ang registration ni ${user.full_name}?`)) {
            router.post(route('admin.users.reject-registration', user.id), {}, { preserveScroll: true });
        }
    };

    const [residentFormMode, setResidentFormMode] = useState('add'); // 'add' or 'edit'
    const [residentEditId, setResidentEditId] = useState(null);
    const residentForm = useForm({
        first_name: '',
        middle_name: '',
        last_name: '',
        suffix: '',
        sex: '',
        date_of_birth: '',
        address: '',
    });
    const importForm = useForm({ import_file: null });

    const openResidentModal = (mode, resident = null) => {
        setResidentFormMode(mode);
        if (mode === 'edit' && resident) {
            setResidentEditId(resident.id);
            residentForm.setData({
                first_name: resident.first_name || '',
                middle_name: resident.middle_name || '',
                last_name: resident.last_name || '',
                suffix: resident.suffix || '',
                sex: resident.sex || '',
                date_of_birth: resident.date_of_birth || '',
                address: resident.address || '',
            });
        } else {
            setResidentEditId(null);
            residentForm.reset();
        }
        residentForm.clearErrors();
        setResidentModalOpen(true);
    };

    const submitResident = (e) => {
        e.preventDefault();
        if (residentFormMode === 'add') {
            residentForm.post(route('admin.census.store'), {
                preserveScroll: true,
                onSuccess: () => setResidentModalOpen(false),
            });
        } else {
            residentForm.post(route('admin.census.update', residentEditId), {
                preserveScroll: true,
                onSuccess: () => setResidentModalOpen(false),
            });
        }
    };

    const [selectedResidents, setSelectedResidents] = useState([]);
    const [censusDeleteModalOpen, setCensusDeleteModalOpen] = useState(false);
    const [censusDeleteConfirm, setCensusDeleteConfirm] = useState('');
    const [censusDeleteTarget, setCensusDeleteTarget] = useState(null); // id or 'batch'

    const handleResidentSelectAll = (e) => {
        if (e.target.checked) {
            setSelectedResidents(censusRecords.data.map((r) => r.id));
        } else {
            setSelectedResidents([]);
        }
    };

    const handleResidentSelect = (id) => {
        setSelectedResidents((prev) =>
            prev.includes(id) ? prev.filter((item) => item !== id) : [...prev, id]
        );
    };

    const openDeleteCensusModal = (target) => {
        setCensusDeleteTarget(target);
        setCensusDeleteConfirm('');
        setCensusDeleteModalOpen(true);
    };

    const submitCensusDelete = (e) => {
        e.preventDefault();
        if (censusDeleteConfirm !== 'DELETE') return;

        if (censusDeleteTarget === 'batch') {
            router.post(
                route('admin.census.batch_destroy'),
                { ids: selectedResidents },
                {
                    preserveScroll: true,
                    onSuccess: () => {
                        setCensusDeleteModalOpen(false);
                        setSelectedResidents([]);
                    },
                }
            );
        } else {
            router.delete(route('admin.census.destroy', censusDeleteTarget), {
                preserveScroll: true,
                onSuccess: () => setCensusDeleteModalOpen(false),
            });
        }
    };

    const submitImport = (e) => {
        e.preventDefault();
        importForm.post(route('admin.census.import'), {
            preserveScroll: true,
            onSuccess: () => {
                setImportModalOpen(false);
                importForm.reset();
            },
        });
    };

    // Queue Filters & Search State
    const [qStatus, setQStatus] = useState(filters?.queue_status || 'all');
    const [qDoc, setQDoc] = useState(filters?.queue_doc || 'all');
    const [qSort, setQSort] = useState(filters?.queue_sort || 'oldest');
    const [qSearch, setQSearch] = useState(filters?.queue_search || '');

    useEffect(() => {
        const delayDebounceFn = setTimeout(() => {
            if (
                qStatus !== (filters?.queue_status || 'all') ||
                qDoc !== (filters?.queue_doc || 'all') ||
                qSort !== (filters?.queue_sort || 'oldest') ||
                qSearch !== (filters?.queue_search || '')
            ) {
                router.get(
                    route('admin.dashboard'),
                    {
                        ...filters,
                        queue_status: qStatus,
                        queue_doc: qDoc,
                        queue_sort: qSort,
                        queue_search: qSearch,
                    },
                    {
                        preserveState: true,
                        preserveScroll: true,
                        only: ['activeQueue', 'receivedQueue', 'filters'],
                    }
                );
            }
        }, 300); // 300ms debounce to prevent server lag while typing

        return () => clearTimeout(delayDebounceFn);
    }, [qStatus, qDoc, qSort, qSearch]);

    useEffect(() => {
        const delayDebounceFn = setTimeout(() => {
            if (residentSearch !== (filters?.resident_search || '')) {
                router.get(
                    route('admin.dashboard'),
                    { ...filters, resident_search: residentSearch },
                    {
                        preserveState: true,
                        preserveScroll: true,
                        only: ['censusRecords', 'filters'],
                    }
                );
            }
        }, 300);
        return () => clearTimeout(delayDebounceFn);
    }, [residentSearch]);

    useEffect(() => {
        const delayDebounceFn = setTimeout(() => {
            if (accountSearch !== (filters?.account_search || '')) {
                router.get(
                    route('admin.dashboard'),
                    { ...filters, account_search: accountSearch },
                    {
                        preserveState: true,
                        preserveScroll: true,
                        only: ['residentAccounts', 'filters'],
                    }
                );
            }
        }, 300);
        return () => clearTimeout(delayDebounceFn);
    }, [accountSearch]);

    // --- EXPANDABLE ROW STATES ---
    const [expandedRows, setExpandedRows] = useState([]);
    const toggleRow = (id, e) => {
        if (e.target.closest('button') || e.target.closest('input')) return;
        setExpandedRows((prev) =>
            prev.includes(id) ? prev.filter((rowId) => rowId !== id) : [...prev, id]
        );
    };

    // --- BATCH PROCESSING STATES ---
    const [selectedRequests, setSelectedRequests] = useState([]);

    const toggleSelectAll = (e) => {
        if (e.target.checked && activeQueue.data) {
            setSelectedRequests(activeQueue.data.map((q) => q.id));
        } else {
            setSelectedRequests([]);
        }
    };

    const toggleSelectOne = (id) => {
        setSelectedRequests((prev) =>
            prev.includes(id) ? prev.filter((reqId) => reqId !== id) : [...prev, id]
        );
    };

    // THE FIX: Modified to accept specific, context-aware valid IDs
    const [batchModal, setBatchModal] = useState({
        isOpen: false,
        nextStatus: '',
        specificIds: [],
    });

    const submitBatchAction = (newStatus, specificIds) => {
        if (!specificIds || specificIds.length === 0) return;
        setBatchModal({ isOpen: true, nextStatus: newStatus, specificIds });
    };

    const confirmBatchAction = () => {
        router.post(
            route('admin.request.batch_update'),
            {
                request_ids: batchModal.specificIds,
                status: batchModal.nextStatus,
            },
            {
                preserveScroll: true,
                onSuccess: () => {
                    setSelectedRequests([]);
                    setBatchModal({ isOpen: false, nextStatus: '', specificIds: [] });
                },
            }
        );
    };

    // --- DOCUMENT MANAGEMENT STATES ---
    const [docModal, setDocModal] = useState({ isOpen: false, mode: 'add', docId: null });
    const docForm = useForm({
        name: '',
        requirements_description: '',
        processing_fee: 0,
        processing_time_minutes: 30,
    });

    const openDocModal = (mode, doc = null) => {
        if (mode === 'edit' && doc) {
            docForm.setData({
                name: doc.name,
                requirements_description: doc.requirements_description,
                processing_fee: doc.processing_fee,
                processing_time_minutes: doc.processing_time_minutes,
            });
            setDocModal({ isOpen: true, mode: 'edit', docId: doc.id });
        } else {
            docForm.reset();
            setDocModal({ isOpen: true, mode: 'add', docId: null });
        }
        docForm.clearErrors();
    };

    const submitDoc = (e) => {
        e.preventDefault();
        if (docModal.mode === 'add') {
            docForm.post(route('admin.documents.store'), {
                preserveScroll: true,
                onSuccess: () => setDocModal({ isOpen: false, mode: 'add', docId: null }),
            });
        } else {
            docForm.post(route('admin.documents.update', docModal.docId), {
                preserveScroll: true,
                onSuccess: () => setDocModal({ isOpen: false, mode: 'add', docId: null }),
            });
        }
    };

    const toggleDocStatus = (docId) => {
        router.post(route('admin.documents.toggle', docId), {}, { preserveScroll: true });
    };

    const [statusModal, setStatusModal] = useState({
        isOpen: false,
        requestId: null,
        nextStatus: '',
        label: '',
    });

    const [pdfModalOpen, setPdfModalOpen] = useState(false);
    const [logbookModalOpen, setLogbookModalOpen] = useState(false);
    const [logbookUrl, setLogbookUrl] = useState('');

    const [localToast, setLocalToast] = useState({ visible: false, message: '' });
    const triggerToast = (msg) => {
        setLocalToast({ visible: true, message: msg });
        setTimeout(() => setLocalToast({ visible: false, message: '' }), 5000);
    };

    // ==========================================
    // BUG FIX 1: Separated Transform and Post!
    // ==========================================
    const statusForm = useForm({ status: '' });
    const submitStatus = (e) => {
        e.preventDefault();
        statusForm.transform((data) => ({ ...data, status: statusModal.nextStatus }));
        statusForm.post(route('admin.request.update_status', statusModal.requestId), {
            preserveScroll: true,
            onSuccess: () =>
                setStatusModal({ isOpen: false, requestId: null, nextStatus: '', label: '' }),
            onError: (errs) => {
                if (Object.keys(errs).length > 0) triggerToast(Object.values(errs));
            },
        });
    };

    const announcementForm = useForm({ message_body: '' });
    const [isLinkDetected, setIsLinkDetected] = useState(false);

    const handleAnnouncementChange = (e) => {
        const val = e.target.value;
        announcementForm.setData('message_body', val);
        setIsLinkDetected(/(http|https|www\.)/i.test(val));
        announcementForm.clearErrors('message_body');
    };

    // --- ADVANCED SMS PROGRESS LOGIC (OPTIMIZED & SYNCED) ---
    const prefixText = 'Dona Lucia Services: '; // From .env
    const prefixLength = prefixText.length; // 21 chars

    const rawText = announcementForm.data.message_body || '';

    // 1. Gayahin ang Backend Sanitizer para 100% accurate ang bilang
    const cleanTextForCounting = rawText
        .replace(
            /[\u{1F600}-\u{1F64F}\u{1F300}-\u{1F5FF}\u{1F680}-\u{1F6FF}\u{2600}-\u{26FF}\u{2700}-\u{27BF}]/gu,
            ''
        ) // Ignore emojis
        .replace(/[ \t]+/g, ' ') // Compress double spaces
        .replace(/[\r\n]{3,}/g, '\n\n') // Limit newlines
        .replace(/[“”]/g, '"')
        .replace(/[‘’]/g, "'") // Convert smart quotes
        .trim();

    const typedLength = cleanTextForCounting.length;
    const totalLength = typedLength > 0 ? typedLength + prefixLength : 0;

    let credits = 0;
    let maxLimit = 150;

    if (totalLength === 0) {
        credits = 0;
        maxLimit = 150;
    } else if (totalLength <= 150) {
        credits = 1;
        maxLimit = 150;
    } else {
        credits = Math.ceil(totalLength / 144);
        maxLimit = credits * 144;
    }

    const rawPercentage = maxLimit > 0 ? (totalLength / maxLimit) * 100 : 0;
    const progressPercentage = Math.min(rawPercentage, 100);

    let progressColor = 'bg-green-500';
    if (credits > 1) {
        progressColor = 'bg-amber-500';
    } else if (progressPercentage >= 90) {
        progressColor = 'bg-orange-500';
    }
    const currentHour = new Date().getHours();
    const isCurfew = currentHour >= 21 || currentHour < 7;

    const submitAnnouncement = (e) => {
        e.preventDefault();
        announcementForm.post(route('admin.announcements.broadcast'), {
            preserveScroll: true,
            onSuccess: () => announcementForm.reset(),
        });
    };

    const walkinStoreForm = useForm({
        contact_number: '',
        first_name: '',
        middle_name: '',
        last_name: '',
        suffix: '',
        sex: '',
        date_of_birth: '',
        address: '',
        document_type_id: '',
        purpose: '',
    });

    const submitWalkinStore = (e) => {
        e.preventDefault();
        walkinStoreForm.post(route('admin.walkin.store'), {
            preserveScroll: true,
            onSuccess: () => {
                walkinStoreForm.reset();
                setActiveTab('queue');
            },
        });
    };

    const passwordForm = useForm({ current_password: '', password: '', password_confirmation: '' });
    const submitPasswordUpdate = (e) => {
        e.preventDefault();
        passwordForm.post(route('password.update'), {
            preserveScroll: true,
            onSuccess: () => passwordForm.reset(),
        });
    };

    const [reportMonth, setReportMonth] = useState('all');
    const [reportYear, setReportYear] = useState(new Date().getFullYear().toString());

    // --- SEARCH STATE FOR LOGS ---
    const [auditSearch, setAuditSearch] = useState(filters?.audit_search || '');

    const handleAuditSearch = (e) => {
        e.preventDefault();
        router.get(
            route('admin.dashboard'),
            { audit_search: auditSearch },
            {
                preserveState: true,
                preserveScroll: true,
                only: ['auditLogs', 'filters'],
            }
        );
    };
    // ==========================================
    // BUG FIX 5: Asynchronous Iframe Rendering Check!
    // ==========================================
    const openLogbook = async () => {
        setLogbookModalOpen(true);
        // Hintaying ma-render ang Iframe bago hanapin (100ms)
        setTimeout(() => {
            const url = route('admin.queue.print_logbook');
            document.getElementById('logbookViewerFrame').src = url;
            setLogbookUrl(url + '?download=1');
        }, 100);
    };

    const submitGeneratePdf = (e) => {
        e.preventDefault();
        const formTarget = e.target;
        setPdfModalOpen(true);
        // Hintaying lumitaw ang Iframe modal bago i-submit
        setTimeout(() => {
            formTarget.submit();
        }, 100);
    };

    const closePdfModal = () => {
        setPdfModalOpen(false);
        document.getElementById('pdfViewerFrame').src = 'about:blank';
    };

    const closeLogbookModal = () => {
        setLogbookModalOpen(false);
        document.getElementById('logbookViewerFrame').src = 'about:blank';
    };

    const handleSearchSort = (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);
        router.get(route('admin.dashboard'), Object.fromEntries(formData.entries()), {
            preserveState: true,
        });
    };

    const urlParams = new URLSearchParams(window.location.search);
    const searchParam = urlParams.get('search') || '';
    const sortParam = urlParams.get('sort') || 'latest';

    // THE FIX: Memoize ang mabigat na Census Masterlist rendering para hindi mag-lag ang buong page kapag nagta-type sa Walk-in forms
    const renderedMasterlist = useMemo(() => {
        if (!censusRecords?.data || censusRecords.data.length === 0) {
            return (
                <tr>
                    <td colSpan="5" className="p-12 text-center font-bold text-slate-400 italic">
                        Walang nahanap na residente.
                    </td>
                </tr>
            );
        }

        return censusRecords.data.map((resident) => (
            <tr key={resident.id} className="transition-colors hover:bg-slate-50">
                <td className="p-4 text-center">
                    <input
                        type="checkbox"
                        className="h-4 w-4 rounded border-slate-300 text-slate-900 focus:ring-slate-900"
                        checked={selectedResidents.includes(resident.id)}
                        onChange={() => handleResidentSelect(resident.id)}
                    />
                </td>
                <td className="p-4">
                    <p className="text-sm font-bold text-slate-900 uppercase">
                        {resident.last_name}, {resident.first_name}{' '}
                        {resident.middle_name ? resident.middle_name.charAt(0) + '.' : ''}{' '}
                        {resident.suffix || ''}
                    </p>
                </td>
                <td className="p-4">
                    <p className="text-xs font-bold text-slate-700">
                        {resident.sex} | {calculateAge(resident.date_of_birth)} yrs old
                    </p>
                </td>
                <td className="p-4">
                    <p className="text-xs font-medium text-slate-600">{resident.address}</p>
                </td>
                <td className="flex justify-end gap-2 p-4 text-right">
                    <button
                        onClick={() => openResidentModal('edit', resident)}
                        className="rounded-lg bg-blue-50 px-3 py-1.5 text-[10px] font-black text-blue-600 uppercase transition-all hover:bg-blue-100 active:scale-95"
                    >
                        Edit
                    </button>
                    <button
                        onClick={() => openDeleteCensusModal(resident.id)}
                        className="rounded-lg bg-red-50 px-3 py-1.5 text-[10px] font-black text-red-600 uppercase transition-all hover:bg-red-100 active:scale-95"
                    >
                        Delete
                    </button>
                </td>
            </tr>
        ));
    }, [censusRecords.data, selectedResidents]);

    return (
        <AdminLayout activeTab={activeTab} setActiveTab={setActiveTab}>
            <Head title="Admin Dashboard - BDLS" />

            <div className="pointer-events-none fixed top-24 left-1/2 z-[60] flex w-full max-w-md -translate-x-1/2 transform flex-col gap-3 px-4">
                <div
                    className={`pointer-events-auto flex items-center gap-4 rounded-xl border-l-4 border-red-500 bg-slate-900 px-6 py-4 text-white shadow-2xl transition-all duration-500 ${localToast.visible ? 'translate-y-0 opacity-100' : '-translate-y-20 opacity-0'}`}
                >
                    <svg
                        className="h-6 w-6 shrink-0 text-red-500"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            strokeLinecap="round"
                            strokeLinejoin="round"
                            strokeWidth="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
                        ></path>
                    </svg>
                    <div>
                        <p className="text-sm font-bold">Error</p>
                        <p className="text-sm font-medium">{localToast.message}</p>
                    </div>
                </div>
            </div>

            {/* --- TAB 2: QUEUE & PROCESSING --- */}
            {activeTab === 'queue' && (
                <div className="animate-in fade-in duration-500">
                    <div className="mt-6 mb-6 flex gap-2 overflow-x-auto pb-2">
                        <button
                            onClick={() => setQueueSubTab('queue-active')}
                            className={`rounded-full px-5 py-2 text-sm font-bold whitespace-nowrap transition-all ${queueSubTab === 'queue-active' ? 'border border-slate-900 bg-slate-900 text-white' : 'border border-transparent bg-slate-200 text-slate-700 hover:bg-slate-300'}`}
                        >
                            Active Queue ({activeQueue.data?.length || 0})
                        </button>
                        <button
                            onClick={() => setQueueSubTab('queue-received')}
                            className={`rounded-full px-5 py-2 text-sm font-bold whitespace-nowrap transition-all ${queueSubTab === 'queue-received' ? 'border border-red-300 bg-red-100 text-red-700' : 'border border-transparent bg-slate-200 text-slate-700 hover:bg-slate-300'}`}
                        >
                            Received History ({receivedQueue.data?.length || 0})
                        </button>
                    </div>

                    {queueSubTab === 'queue-active' && (
                        <div>
                            {/* QUEUE FILTERS & SEARCH */}
                            <div className="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center">
                                <div className="flex-1">
                                    <input
                                        type="text"
                                        placeholder="I-search ang Queue #, Residente, o Dokumento..."
                                        value={qSearch}
                                        onChange={(e) => setQSearch(e.target.value)}
                                        className="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs font-bold text-slate-700 outline-none focus:border-slate-900 focus:ring-1 focus:ring-slate-900 sm:max-w-xs"
                                    />
                                </div>
                                <select
                                    value={qStatus}
                                    onChange={(e) => setQStatus(e.target.value)}
                                    className="rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs font-bold text-slate-700 outline-none focus:border-slate-900 focus:ring-1 focus:ring-slate-900"
                                >
                                    <option value="all">Lahat ng Status</option>
                                    <option value="pending">Pending</option>
                                    <option value="processing">Processing</option>
                                    <option value="for_interview">For Interview</option>
                                    <option value="released">Ready for Release</option>
                                </select>
                                <select
                                    value={qDoc}
                                    onChange={(e) => setQDoc(e.target.value)}
                                    className="rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs font-bold text-slate-700 outline-none focus:border-slate-900 focus:ring-1 focus:ring-slate-900"
                                >
                                    <option value="all">Group by Document</option>
                                    {documents.map((doc) => (
                                        <option key={doc.id} value={doc.id}>
                                            {doc.name}
                                        </option>
                                    ))}
                                </select>
                                <select
                                    value={qSort}
                                    onChange={(e) => setQSort(e.target.value)}
                                    className="rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs font-bold text-slate-700 outline-none focus:border-slate-900 focus:ring-1 focus:ring-slate-900"
                                >
                                    <option value="oldest">Pila: Luma (Ascending)</option>
                                    <option value="newest">Pila: Bago (Descending)</option>
                                </select>
                            </div>
                            <div className="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                                <div className="relative max-h-[60vh] overflow-y-auto">
                                    <table className="w-full border-collapse text-left">
                                        <thead className="sticky top-0 z-20 bg-slate-50 shadow-sm">
                                            <tr className="border-b border-slate-200 text-[10px] tracking-[0.15em] text-slate-500 uppercase">
                                                <th className="w-12 p-4 text-center">
                                                    <input
                                                        type="checkbox"
                                                        onChange={toggleSelectAll}
                                                        checked={
                                                            activeQueue.data?.length > 0 &&
                                                            selectedRequests.length ===
                                                                activeQueue.data?.length
                                                        }
                                                        className="h-4 w-4 cursor-pointer rounded border-slate-300 text-red-600 focus:ring-red-600"
                                                    />
                                                </th>
                                                <th className="p-4 font-black">Queue #</th>
                                                <th className="p-4 font-black">Residente</th>
                                                <th className="p-4 font-black">Dokumento</th>
                                                <th className="p-4 font-black">Status</th>
                                                <th className="p-4 text-right font-black">
                                                    Aksyon
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody className="divide-y divide-slate-100">
                                            {activeQueue.data && activeQueue.data.length > 0 ? (
                                                activeQueue.data.map((queue) => {
                                                    const rawStatus = queue.status.toLowerCase();
                                                    // THE FIX: Specific IDs that strictly require probing interview based on the manual
                                                    const interviewDocs = [
                                                        3, 4, 5, 6, 8, 9, 10, 11,
                                                    ];
                                                    const isInterviewDoc = interviewDocs.includes(
                                                        queue.document_type_id
                                                    );

                                                    let badgeClass = '';
                                                    if (rawStatus === 'pending')
                                                        badgeClass =
                                                            'bg-yellow-100 text-yellow-700 border border-yellow-200';
                                                    if (rawStatus === 'processing')
                                                        badgeClass =
                                                            'bg-blue-100 text-blue-700 border border-blue-200';
                                                    if (rawStatus === 'for_interview')
                                                        badgeClass =
                                                            'bg-purple-100 text-purple-700 border border-purple-200';
                                                    if (rawStatus === 'released')
                                                        badgeClass =
                                                            'bg-orange-100 text-orange-700 border border-orange-200';
                                                    if (
                                                        rawStatus === 'rejected' ||
                                                        rawStatus === 'canceled'
                                                    )
                                                        badgeClass =
                                                            'bg-red-100 text-red-700 border border-red-200';

                                                    return (
                                                        <React.Fragment key={queue.id}>
                                                        <tr
                                                            onClick={(e) => toggleRow(queue.id, e)}
                                                            className={`cursor-pointer transition-colors hover:bg-slate-50 ${selectedRequests.includes(queue.id) ? 'bg-red-50/50' : ''}`}
                                                        >
                                                            <td className="p-4 text-center">
                                                                <input
                                                                    type="checkbox"
                                                                    checked={selectedRequests.includes(
                                                                        queue.id
                                                                    )}
                                                                    onChange={() =>
                                                                        toggleSelectOne(queue.id)
                                                                    }
                                                                    className="h-4 w-4 cursor-pointer rounded border-slate-300 text-red-600 focus:ring-red-600"
                                                                />
                                                            </td>
                                                            <td className="p-4 text-xl font-black tracking-tighter text-slate-900">
                                                                {queue.queue_number}
                                                            </td>
                                                            <td className="p-4">
                                                                <p className="mb-1 text-sm leading-none font-bold text-slate-900 uppercase">
                                                                    {queue.user?.full_name || 'WALANG PANGALAN'}
                                                                </p>
                                                                <p className="font-mono text-[10px] tracking-tight text-slate-500">
                                                                    {queue.user?.contact_number}
                                                                </p>
                                                            </td>
                                                            <td className="p-4 text-xs font-bold text-slate-700 uppercase">
                                                                {queue.document_type?.name ?? 'N/A'}
                                                                {queue.payment_method === 'GCash' &&
                                                                    queue.payment_receipt_path && (
                                                                        <button
                                                                            type="button"
                                                                            onClick={() => {
                                                                                setSelectedReceiptPath(
                                                                                    queue.payment_receipt_path
                                                                                );
                                                                                setReceiptModalOpen(
                                                                                    true
                                                                                );
                                                                            }}
                                                                            className="mt-2.5 flex items-center gap-1.5 rounded-lg border border-blue-200 bg-blue-50 px-2.5 py-1 text-[9px] font-black tracking-widest text-blue-700 uppercase shadow-sm transition-all hover:bg-blue-100"
                                                                        >
                                                                            View Receipt 📄
                                                                        </button>
                                                                    )}
                                                            </td>
                                                            <td className="p-4">
                                                                <span
                                                                    className={`rounded-full px-3 py-1 text-[9px] font-black tracking-widest uppercase shadow-sm ${badgeClass}`}
                                                                >
                                                                    {rawStatus.replace('_', ' ')}
                                                                </span>
                                                            </td>
                                                            <td className="p-4 align-middle">
                                                                <div className="flex flex-col items-end gap-2">
                                                                    {/* PENDING STATE */}
                                                                    {rawStatus === 'pending' && (
                                                                        <button
                                                                            onClick={() =>
                                                                                setStatusModal({
                                                                                    isOpen: true,
                                                                                    requestId:
                                                                                        queue.id,
                                                                                    nextStatus:
                                                                                        'processing',
                                                                                    label: 'Process Request',
                                                                                })
                                                                            }
                                                                            className="w-36 rounded-lg bg-slate-900 py-2 text-center text-[10px] font-black tracking-widest text-white uppercase shadow-sm transition-all hover:bg-slate-800 active:scale-95"
                                                                        >
                                                                            Process Request
                                                                        </button>
                                                                    )}

                                                                    {/* PROCESSING STATE WITH CHOICES (FOR INTERVIEW DOCS) */}
                                                                    {rawStatus === 'processing' &&
                                                                        isInterviewDoc && (
                                                                            <>
                                                                                <button
                                                                                    onClick={() =>
                                                                                        setStatusModal(
                                                                                            {
                                                                                                isOpen: true,
                                                                                                requestId:
                                                                                                    queue.id,
                                                                                                nextStatus:
                                                                                                    'for_interview',
                                                                                                label: 'Set for Interview',
                                                                                            }
                                                                                        )
                                                                                    }
                                                                                    className="w-36 rounded-lg bg-purple-600 py-2 text-center text-[10px] font-black tracking-widest text-white uppercase shadow-sm transition-all hover:bg-purple-700 active:scale-95"
                                                                                >
                                                                                    Set for
                                                                                    Interview
                                                                                </button>
                                                                                <button
                                                                                    onClick={() =>
                                                                                        setStatusModal(
                                                                                            {
                                                                                                isOpen: true,
                                                                                                requestId:
                                                                                                    queue.id,
                                                                                                nextStatus:
                                                                                                    'released',
                                                                                                label: 'Release Document',
                                                                                            }
                                                                                        )
                                                                                    }
                                                                                    className="w-36 rounded-lg bg-green-600 py-2 text-center text-[10px] font-black tracking-widest text-white uppercase shadow-sm transition-all hover:bg-green-700 active:scale-95"
                                                                                >
                                                                                    Release Document
                                                                                </button>
                                                                            </>
                                                                        )}

                                                                    {/* PROCESSING STATE (NON-INTERVIEW DOCS) */}
                                                                    {rawStatus === 'processing' &&
                                                                        !isInterviewDoc && (
                                                                            <button
                                                                                onClick={() =>
                                                                                    setStatusModal({
                                                                                        isOpen: true,
                                                                                        requestId:
                                                                                            queue.id,
                                                                                        nextStatus:
                                                                                            'released',
                                                                                        label: 'Release Document',
                                                                                    })
                                                                                }
                                                                                className="w-36 rounded-lg bg-green-600 py-2 text-center text-[10px] font-black tracking-widest text-white uppercase shadow-sm transition-all hover:bg-green-700 active:scale-95"
                                                                            >
                                                                                Release Document
                                                                            </button>
                                                                        )}

                                                                    {/* FOR INTERVIEW STATE */}
                                                                    {rawStatus ===
                                                                        'for_interview' && (
                                                                        <button
                                                                            onClick={() =>
                                                                                setStatusModal({
                                                                                    isOpen: true,
                                                                                    requestId:
                                                                                        queue.id,
                                                                                    nextStatus:
                                                                                        'released',
                                                                                    label: 'Release Document',
                                                                                })
                                                                            }
                                                                            className="w-36 rounded-lg bg-green-600 py-2 text-center text-[10px] font-black tracking-widest text-white uppercase shadow-sm transition-all hover:bg-green-700 active:scale-95"
                                                                        >
                                                                            Release Document
                                                                        </button>
                                                                    )}

                                                                    {/* RELEASED STATE */}
                                                                    {rawStatus === 'released' && (
                                                                        <button
                                                                            onClick={() =>
                                                                                setStatusModal({
                                                                                    isOpen: true,
                                                                                    requestId:
                                                                                        queue.id,
                                                                                    nextStatus:
                                                                                        'received',
                                                                                    label: 'Mark as Received',
                                                                                })
                                                                            }
                                                                            className="w-36 rounded-lg bg-slate-900 py-2 text-center text-[10px] font-black tracking-widest text-white uppercase shadow-sm transition-all hover:bg-slate-800 active:scale-95"
                                                                        >
                                                                            Mark as Received
                                                                        </button>
                                                                    )}

                                                                    {/* GLOBAL REJECT BUTTON (Only in Pending/Processing) */}
                                                                    {(rawStatus === 'pending' ||
                                                                        rawStatus ===
                                                                            'processing') && (
                                                                        <button
                                                                            onClick={() =>
                                                                                setStatusModal({
                                                                                    isOpen: true,
                                                                                    requestId:
                                                                                        queue.id,
                                                                                    nextStatus:
                                                                                        'rejected',
                                                                                    label: 'Reject Request',
                                                                                })
                                                                            }
                                                                            className="w-36 rounded-lg border border-red-200 bg-red-50 py-2 text-center text-[10px] font-black tracking-widest text-red-600 uppercase shadow-sm transition-all hover:bg-red-100 active:scale-95"
                                                                        >
                                                                            Reject
                                                                        </button>
                                                                    )}

                                                                    {/* NO ACTION FALLBACK */}
                                                                    {![
                                                                        'pending',
                                                                        'processing',
                                                                        'for_interview',
                                                                        'released',
                                                                    ].includes(rawStatus) && (
                                                                        <span className="mt-2 w-36 text-center text-xs font-bold text-slate-400 italic">
                                                                            No Action
                                                                        </span>
                                                                    )}
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        {expandedRows.includes(queue.id) && (
                                                            <tr className="bg-slate-50 border-b border-slate-100">
                                                                <td colSpan="7" className="p-4 pl-12">
                                                                    <div className="rounded-xl border border-slate-200 bg-white p-4 shadow-sm animate-in fade-in slide-in-from-top-2 duration-200">
                                                                        <div className="grid grid-cols-2 gap-4">
                                                                            <div>
                                                                                <p className="text-[10px] font-black tracking-widest text-slate-400 uppercase">Layunin (Purpose)</p>
                                                                                <p className="mt-1 text-xs font-bold text-slate-700">{queue.purpose}</p>
                                                                            </div>
                                                                            <div>
                                                                                <p className="text-[10px] font-black tracking-widest text-slate-400 uppercase">Karagdagang Detalye</p>
                                                                                <p className="mt-1 text-xs font-medium text-slate-600">{queue.additional_details ? queue.additional_details : 'Walang karagdagang detalye'}</p>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                        )}
                                                        </React.Fragment>
                                                    );
                                                })
                                            ) : (
                                                <tr>
                                                    <td
                                                        colSpan="6"
                                                        className="p-12 text-center font-bold text-slate-400 italic"
                                                    >
                                                        Walang aktibong nakapila.
                                                    </td>
                                                </tr>
                                            )}
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <Pagination links={activeQueue.links} />
                        </div>
                    )}

                    {queueSubTab === 'queue-received' && (
                        <div>
                            <div className="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                                <div className="flex items-center justify-between border-b border-slate-100 bg-slate-50 p-4">
                                    <h3 className="text-sm font-bold tracking-widest text-slate-800 uppercase">
                                        Released Records
                                    </h3>
                                    <button
                                        onClick={openLogbook}
                                        className="flex items-center gap-2 rounded-lg bg-slate-900 px-4 py-2 text-[10px] font-black tracking-widest text-white uppercase shadow-sm transition-all hover:bg-slate-800 active:scale-95"
                                    >
                                        <svg
                                            className="h-4 w-4"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                strokeLinecap="round"
                                                strokeLinejoin="round"
                                                strokeWidth="2"
                                                d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"
                                            ></path>
                                        </svg>
                                        Tingnan ang Logbook
                                    </button>
                                </div>
                                <div className="relative max-h-[60vh] overflow-y-auto">
                                    <table className="w-full border-collapse text-left">
                                        <thead className="sticky top-0 z-20 bg-slate-50 shadow-sm">
                                            <tr className="border-b border-slate-200 text-[10px] tracking-[0.15em] text-slate-500 uppercase">
                                                <th className="p-4 font-black">Queue #</th>
                                                <th className="p-4 font-black">Residente</th>
                                                <th className="p-4 font-black">Dokumento</th>
                                                <th className="p-4 font-black">Date Released</th>
                                                <th className="p-4 text-right font-black">
                                                    Status
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody className="divide-y divide-slate-100">
                                            {receivedQueue.data && receivedQueue.data.length > 0 ? (
                                                receivedQueue.data.map((queue) => (
                                                    <React.Fragment key={queue.id}>
                                                        <tr className="bg-slate-50/50">
                                                        <td className="p-4 text-xl font-black tracking-tighter text-slate-400">
                                                            {queue.queue_number}
                                                        </td>
                                                        <td className="p-4 opacity-75">
                                                            <p className="mb-1 text-sm leading-none font-bold text-slate-900 uppercase">
                                                                {queue.user?.full_name || 'WALANG PANGALAN'}
                                                            </p>
                                                            <p className="font-mono text-[10px] text-slate-500">
                                                                {queue.user?.contact_number}
                                                            </p>
                                                        </td>
                                                        <td className="p-4 text-xs font-bold text-slate-600 uppercase">
                                                            {queue.document_type?.name ?? 'N/A'}
                                                        </td>
                                                        <td className="p-4 text-xs font-bold text-slate-500">
                                                            {queue.released_at
                                                                ? new Date(
                                                                      queue.released_at
                                                                  ).toLocaleString()
                                                                : 'N/A'}
                                                        </td>
                                                        <td className="p-4 text-right">
                                                            <span
                                                                className={`rounded-full border px-3 py-1 text-[9px] font-black tracking-widest uppercase ${queue.status === 'received' ? 'border-green-200 bg-green-100 text-green-700' : 'border-red-200 bg-red-100 text-red-700'}`}
                                                            >
                                                                {queue.status}
                                                            </span>
                                                        </td>
                                                    </tr>
                                                    {expandedRows.includes(queue.id) && (
                                                        <tr className="bg-slate-50 border-b border-slate-100">
                                                            <td colSpan="5" className="p-4 pl-12">
                                                                <div className="rounded-xl border border-slate-200 bg-white p-4 shadow-sm animate-in fade-in slide-in-from-top-2 duration-200">
                                                                    <div className="grid grid-cols-2 gap-4">
                                                                        <div>
                                                                            <p className="text-[10px] font-black tracking-widest text-slate-400 uppercase">Layunin (Purpose)</p>
                                                                            <p className="mt-1 text-xs font-bold text-slate-700">{queue.purpose}</p>
                                                                        </div>
                                                                        <div>
                                                                            <p className="text-[10px] font-black tracking-widest text-slate-400 uppercase">Karagdagang Detalye</p>
                                                                            <p className="mt-1 text-xs font-medium text-slate-600">{queue.additional_details ? queue.additional_details : 'Walang karagdagang detalye'}</p>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    )}
                                                    </React.Fragment>
                                                ))
                                            ) : (
                                                <tr>
                                                    <td
                                                        colSpan="5"
                                                        className="p-12 text-center font-bold text-slate-400 italic"
                                                    >
                                                        Walang record ng release history.
                                                    </td>
                                                </tr>
                                            )}
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <Pagination links={receivedQueue.links} />
                        </div>
                    )}
                </div>
            )}

            {/* --- TAB 3: WALK-IN --- */}
            {activeTab === 'walkin' && (
                <div className="animate-in fade-in duration-500">
                    <div className="mx-auto max-w-4xl rounded-xl border border-slate-200 bg-white p-6 shadow-sm md:p-8">
                        <div className="mb-6 border-b border-slate-100 pb-6 text-center">
                            <div className="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-full bg-slate-100 text-slate-900">
                                <svg
                                    className="h-7 w-7"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        strokeLinecap="round"
                                        strokeLinejoin="round"
                                        strokeWidth="2"
                                        d="M12 4v16m8-8H4"
                                    ></path>
                                </svg>
                            </div>
                            <h2 className="text-2xl font-black tracking-tight text-slate-900 uppercase">
                                Direct Walk-in Encoding
                            </h2>
                            <p className="mt-1 text-sm font-medium text-slate-500">
                                I-type ang impormasyon. Awtomatikong hahanapin ng system kung luma o
                                bagong residente base sa Contact Number.
                            </p>
                        </div>

                        {errors.walkin_error && (
                            <div className="mb-6 rounded-lg bg-red-50 p-4 text-sm font-bold text-red-600">
                                {errors.walkin_error}
                            </div>
                        )}

                        <form onSubmit={submitWalkinStore} className="space-y-6">
                            <div className="grid grid-cols-1 gap-4 sm:grid-cols-3">
                                <div className="relative sm:col-span-3">
                                    <label className="mb-1 block text-[10px] font-black tracking-widest text-slate-400 uppercase">
                                        Contact Number *
                                    </label>
                                    <input
                                        type="text"
                                        value={walkinStoreForm.data.contact_number}
                                        onChange={(e) => {
                                            // THE FIX: Support pasted '+63' numbers
                                            let rawVal = e.target.value;
                                            if (rawVal.startsWith('+63')) {
                                                rawVal = '0' + rawVal.substring(3);
                                            }
                                            const val = rawVal.replace(/[^0-9]/g, '');

                                            // THE FIX: Auto-reset kung binura ang number (poka-yoke)
                                            if (
                                                val.length < 11 &&
                                                walkinStoreForm.data.first_name !== ''
                                            ) {
                                                walkinStoreForm.setData((data) => ({
                                                    ...data,
                                                    contact_number: val,
                                                    first_name: '',
                                                    middle_name: '',
                                                    last_name: '',
                                                    suffix: '',
                                                    sex: 'Male',
                                                    date_of_birth: '',
                                                    address: '',
                                                }));
                                                return;
                                            }

                                            walkinStoreForm.setData('contact_number', val);

                                            // THE FIX: Event-Driven Silent Background Check (No polling!)
                                            if (val.length === 11) {
                                                axios
                                                    .get(`/admin/walkin/check-number/${val}`)
                                                    .then((res) => {
                                                        if (res.data.found) {
                                                            const u = res.data.user;
                                                            // Silent Auto-fill!
                                                            walkinStoreForm.setData((data) => ({
                                                                ...data,
                                                                contact_number: val,
                                                                first_name: u.first_name,
                                                                middle_name: u.middle_name || '',
                                                                last_name: u.last_name,
                                                                suffix: u.suffix || '',
                                                                sex: u.sex,
                                                                date_of_birth: u.date_of_birth,
                                                                address: u.address,
                                                            }));
                                                        }
                                                    })
                                                    .catch((err) =>
                                                        console.error('Check failed silently', err)
                                                    );
                                            }
                                        }}
                                        maxLength="11"
                                        required
                                        placeholder="09XXXXXXXXX"
                                        className="w-full rounded-lg border border-slate-300 bg-slate-50 px-4 py-3 font-mono text-lg font-bold tracking-widest outline-none focus:ring-2 focus:ring-slate-900"
                                    />
                                </div>
                                <div>
                                    <label className="mb-1 block text-[10px] font-black tracking-widest text-slate-400 uppercase">
                                        First Name *
                                    </label>
                                    <input
                                        type="text"
                                        value={walkinStoreForm.data.first_name}
                                        onChange={(e) =>
                                            walkinStoreForm.setData('first_name', e.target.value)
                                        }
                                        required
                                        placeholder="Pangalan"
                                        className="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-bold outline-none focus:ring-2 focus:ring-slate-900"
                                    />
                                </div>
                                <div>
                                    <label className="mb-1 block text-[10px] font-black tracking-widest text-slate-400 uppercase">
                                        Middle Name
                                    </label>
                                    <input
                                        type="text"
                                        value={walkinStoreForm.data.middle_name}
                                        onChange={(e) =>
                                            walkinStoreForm.setData('middle_name', e.target.value)
                                        }
                                        placeholder="Gitnang Pangalan (Opsyonal)"
                                        className="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-bold outline-none focus:ring-2 focus:ring-slate-900"
                                    />
                                </div>
                                <div>
                                    <label className="mb-1 block text-[10px] font-black tracking-widest text-slate-400 uppercase">
                                        Last Name *
                                    </label>
                                    <input
                                        type="text"
                                        value={walkinStoreForm.data.last_name}
                                        onChange={(e) =>
                                            walkinStoreForm.setData('last_name', e.target.value)
                                        }
                                        required
                                        placeholder="Apelyido"
                                        className="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-bold outline-none focus:ring-2 focus:ring-slate-900"
                                    />
                                </div>
                                <div>
                                    <label className="mb-1 block text-[10px] font-black tracking-widest text-slate-400 uppercase">
                                        Suffix
                                    </label>
                                    <select
                                        value={walkinStoreForm.data.suffix}
                                        onChange={(e) =>
                                            walkinStoreForm.setData('suffix', e.target.value)
                                        }
                                        className="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-bold outline-none focus:ring-2 focus:ring-slate-900"
                                    >
                                        <option value="">Wala</option>
                                        <option value="Jr.">Jr.</option>
                                        <option value="Sr.">Sr.</option>
                                        <option value="I">I</option>
                                        <option value="II">II</option>
                                        <option value="III">III</option>
                                        <option value="IV">IV</option>
                                    </select>
                                </div>
                                <div>
                                    <label className="mb-1 block text-[10px] font-black tracking-widest text-slate-400 uppercase">
                                        Sex *
                                    </label>
                                    <select
                                        value={walkinStoreForm.data.sex}
                                        onChange={(e) =>
                                            walkinStoreForm.setData('sex', e.target.value)
                                        }
                                        required
                                        className="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-bold outline-none focus:ring-2 focus:ring-slate-900"
                                    >
                                        <option value="">-- Kasarian --</option>
                                        <option value="Male">Lalaki</option>
                                        <option value="Female">Babae</option>
                                    </select>
                                </div>
                                <div>
                                    <label className="mb-1 block text-[10px] font-black tracking-widest text-slate-400 uppercase">
                                        Date of Birth *
                                    </label>
                                    <input
                                        type="date"
                                        value={walkinStoreForm.data.date_of_birth}
                                        onChange={(e) =>
                                            walkinStoreForm.setData('date_of_birth', e.target.value)
                                        }
                                        required
                                        className="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-bold outline-none focus:ring-2 focus:ring-slate-900"
                                    />
                                </div>
                                <div className="sm:col-span-3">
                                    <label className="mb-1 block text-[10px] font-black tracking-widest text-slate-400 uppercase">
                                        Address *
                                    </label>
                                    <input
                                        type="text"
                                        value={walkinStoreForm.data.address}
                                        onChange={(e) =>
                                            walkinStoreForm.setData('address', e.target.value)
                                        }
                                        required
                                        placeholder="Hal. 123 Purok 1"
                                        className="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-bold outline-none focus:ring-2 focus:ring-slate-900"
                                    />
                                </div>
                            </div>

                            <div className="border-t border-slate-100 pt-6">
                                <div className="flex flex-col gap-3 sm:flex-row">
                                    <select
                                        value={walkinStoreForm.data.document_type_id}
                                        onChange={(e) =>
                                            walkinStoreForm.setData(
                                                'document_type_id',
                                                e.target.value
                                            )
                                        }
                                        required
                                        className="min-w-0 flex-1 rounded-lg border border-amber-300 bg-amber-50 px-4 py-3 text-sm font-bold text-amber-900 outline-none focus:ring-2 focus:ring-amber-600"
                                    >
                                        <option value="">-- Piliin ang Dokumento --</option>
                                        {documents.map((doc) => (
                                            <option key={doc.id} value={doc.id}>
                                                {doc.name}
                                            </option>
                                        ))}
                                    </select>
                                    <input
                                        type="text"
                                        value={walkinStoreForm.data.purpose}
                                        onChange={(e) =>
                                            walkinStoreForm.setData('purpose', e.target.value)
                                        }
                                        placeholder="Layunin (Purpose)"
                                        required
                                        className="min-w-0 flex-1 rounded-lg border border-amber-300 bg-amber-50 px-4 py-3 text-sm font-bold text-amber-900 outline-none focus:ring-2 focus:ring-amber-600"
                                    />
                                    <button
                                        type="submit"
                                        disabled={walkinStoreForm.processing}
                                        className="shrink-0 rounded-lg bg-slate-900 px-8 py-3 text-[11px] font-black tracking-widest whitespace-nowrap text-white uppercase shadow-md transition-all hover:bg-slate-800 active:scale-95 disabled:opacity-50"
                                    >
                                        {walkinStoreForm.processing
                                            ? 'Sinasave...'
                                            : 'I-save & Create Queue'}
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            )}

            {/* --- TAB 4: ANNOUNCEMENTS --- */}
            {activeTab === 'announcements' && (
                <div className="animate-in fade-in duration-500">
                    <div className="mx-auto mt-8 max-w-3xl">
                        <div className="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm md:p-8">
                            <div className="mb-2 flex items-center gap-3">
                                <div className="flex h-10 w-10 items-center justify-center rounded-lg bg-slate-900 text-white">
                                    <svg
                                        className="h-5 w-5"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            strokeLinecap="round"
                                            strokeLinejoin="round"
                                            strokeWidth="2"
                                            d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"
                                        ></path>
                                    </svg>
                                </div>
                                <h2 className="text-2xl font-black tracking-tight text-slate-900 uppercase">
                                    Broadcast Announcement
                                </h2>
                            </div>
                            <p className="mb-6 border-b border-slate-100 pb-6 text-sm font-medium text-slate-500">
                                Magpadala ng text blast sa lahat ng verified residents ng Barangay.{' '}
                                <br />
                                <span className="font-bold text-amber-600">
                                    Ayon sa NTC: Bawal ang links at bawal mag-send mula 9:00 PM
                                    hanggang 7:00 AM.
                                </span>
                            </p>

                            {isCurfew || errors.curfew ? (
                                <div className="mb-6 rounded-r-xl border-l-4 border-red-500 bg-red-50 p-4 shadow-sm">
                                    <div className="mb-1 flex items-center gap-2 text-xs font-black tracking-widest text-red-700 uppercase">
                                        <svg
                                            className="h-5 w-5"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                strokeLinecap="round"
                                                strokeLinejoin="round"
                                                strokeWidth="2"
                                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
                                            ></path>
                                        </svg>
                                        NTC SMS Curfew Active
                                    </div>
                                    <p className="text-sm font-bold text-red-600">
                                        Pansamantalang naka-disable ang Broadcast (9:00 PM - 7:00
                                        AM) upang sumunod sa Anti-Spam rules. Subukan muli bukas.
                                    </p>
                                </div>
                            ) : null}

                            <form onSubmit={submitAnnouncement}>
                                <div className="mb-4">
                                    <label className="mb-2 block text-[10px] font-black tracking-widest text-slate-400 uppercase">
                                        Mensahe (Message Body){' '}
                                        <span className="text-red-500">*</span>
                                    </label>
                                    {/* NTC PREFIX BADGE */}
                                    <div className="mb-2 flex items-center justify-between rounded-lg border border-slate-200 bg-slate-100 p-2.5 shadow-sm">
                                        <div className="flex items-center gap-2">
                                            <span className="rounded bg-slate-300 px-2 py-1 text-[9px] font-black tracking-widest text-slate-700 uppercase">
                                                NTC Prefix
                                            </span>
                                            <span className="font-mono text-xs font-bold text-slate-900">
                                                "{prefixText}"
                                            </span>
                                        </div>
                                        <span className="text-[10px] font-bold text-slate-500">
                                            +{prefixLength} Characters
                                        </span>
                                    </div>
                                    <textarea
                                        value={announcementForm.data.message_body}
                                        onChange={handleAnnouncementChange}
                                        rows="5"
                                        required
                                        disabled={isCurfew}
                                        placeholder={
                                            isCurfew
                                                ? 'Naka-disable ang pag-type tuwing curfew...'
                                                : 'I-type ang iyong anunsyo dito...'
                                        }
                                        className={`w-full rounded-xl border px-4 py-3 ${announcementForm.errors.message_body || isLinkDetected ? 'border-red-500 bg-red-50 ring-1 ring-red-500' : 'border-slate-300 bg-slate-50'} resize-none font-medium text-slate-800 transition-all outline-none focus:ring-2 focus:ring-slate-900 ${isCurfew ? 'cursor-not-allowed bg-slate-100 opacity-60' : ''}`}
                                    ></textarea>

                                    {announcementForm.errors.message_body && (
                                        <p className="mt-2 text-xs font-bold text-red-600">
                                            {announcementForm.errors.message_body}
                                        </p>
                                    )}

                                    {/* VISUAL PROGRESS BAR & COUNTERS */}
                                    <div className="mt-3 flex flex-col gap-2">
                                        <div className="h-2 w-full overflow-hidden rounded-full bg-slate-200">
                                            <div
                                                className={`h-full transition-all duration-300 ${progressColor}`}
                                                style={{ width: `${progressPercentage}%` }}
                                            ></div>
                                        </div>
                                        <div className="flex items-start justify-between">
                                            <p
                                                className={`mt-1 text-xs font-bold text-red-600 ${isLinkDetected ? 'block' : 'hidden'}`}
                                            >
                                                ⚠️ Bawal mag-send ng links (http/www).
                                            </p>
                                            {totalLength === 0 ? (
                                                <p className="mt-1 ml-auto text-right text-xs font-medium text-slate-500">
                                                    Mag-type para makita ang bilang...
                                                </p>
                                            ) : (
                                                <div className="ml-auto text-right text-xs font-medium text-slate-500">
                                                    <span
                                                        className={`font-bold ${credits > 1 ? 'text-amber-600' : 'text-slate-700'}`}
                                                    >
                                                        {totalLength} / {maxLimit} Chars
                                                    </span>
                                                    <br />
                                                    <span
                                                        className={`text-[10px] font-black tracking-widest uppercase ${credits > 1 ? 'text-amber-600' : 'text-green-600'}`}
                                                    >
                                                        (EST. {credits} CREDIT/S PER USER)
                                                    </span>
                                                </div>
                                            )}
                                        </div>
                                    </div>
                                </div>

                                <div className="flex justify-end pt-2">
                                    <button
                                        type="submit"
                                        disabled={
                                            isCurfew ||
                                            isLinkDetected ||
                                            announcementForm.processing
                                        }
                                        className={`flex items-center gap-2 rounded-xl bg-slate-900 px-8 py-3.5 text-xs font-black tracking-widest text-white uppercase shadow-md transition-all hover:bg-slate-800 ${isCurfew || isLinkDetected || announcementForm.processing ? 'cursor-not-allowed opacity-50 hover:bg-slate-900 active:scale-100' : 'active:scale-95'}`}
                                    >
                                        {announcementForm.processing
                                            ? 'Sending...'
                                            : 'Send Broadcast'}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            )}

            {/* --- TAB: MANAGE ACCOUNTS --- */}
            {activeTab === 'accounts' && (
                <div className="animate-in fade-in duration-500">
                    <div className="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                        {/* HEADER */}
                        <div className="flex flex-col items-start justify-between gap-4 border-b border-slate-200 bg-slate-50 p-6 sm:flex-row sm:items-center">
                            <div>
                                <h2 className="text-xl font-black tracking-tight text-slate-900 uppercase">
                                    Manage Accounts
                                </h2>
                                <p className="text-sm text-slate-500">
                                    I-monitor at pamahalaan ang mga nakarehistrong residente
                                    (Verified, Unverified, Suspended).
                                </p>
                            </div>
                        </div>

                        {/* SUB-TABS NAVIGATION */}
                        <div className="flex gap-2 border-b border-slate-200 bg-white px-6 py-3">
                            <button
                                onClick={() => setAccountsSubTab('list')}
                                className={`rounded-lg px-4 py-2 text-[10px] font-black tracking-widest uppercase transition-all ${accountsSubTab === 'list' ? 'bg-slate-900 text-white shadow-sm' : 'text-slate-500 hover:bg-slate-100'}`}
                            >
                                Accounts List
                            </button>
                            <button
                                onClick={() => setAccountsSubTab('analytics')}
                                className={`rounded-lg px-4 py-2 text-[10px] font-black tracking-widest uppercase transition-all ${accountsSubTab === 'analytics' ? 'bg-slate-900 text-white shadow-sm' : 'text-slate-500 hover:bg-slate-100'}`}
                            >
                                Analytics & Reports
                            </button>
                            <button
                                onClick={() => setAccountsSubTab('pending')}
                                className={`rounded-lg px-4 py-2 text-[10px] font-black tracking-widest uppercase transition-all ${accountsSubTab === 'pending' ? 'bg-slate-900 text-white shadow-sm' : 'text-slate-500 hover:bg-slate-100'}`}
                            >
                                Pending Registrations
                            </button>
                        </div>

                        {accountsSubTab === 'list' && (
                            <div>
                                {/* SEARCH BAR */}
                                <div className="border-b border-slate-100 bg-white p-4">
                                    <input
                                        type="text"
                                        placeholder="I-search ang pangalan, number, o email..."
                                        value={accountSearch}
                                        onChange={(e) => setAccountSearch(e.target.value)}
                                        className="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm font-medium text-slate-700 outline-none focus:border-slate-500 focus:ring-2 focus:ring-slate-500 sm:max-w-md"
                                    />
                                </div>

                                {/* ACCOUNTS TABLE */}
                                <div className="overflow-x-auto">
                                    <table className="w-full border-collapse text-left">
                                        <thead>
                                            <tr className="border-b border-slate-200 bg-slate-100 text-[10px] tracking-[0.15em] text-slate-500 uppercase">
                                                <th className="p-4 font-black">Pangalan</th>
                                                <th className="p-4 font-black">Contact & Email</th>
                                                <th className="p-4 font-black">Account Status</th>
                                                <th className="p-4 text-right font-black">
                                                    Aksyon
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody className="divide-y divide-slate-100">
                                            {residentAccounts.data &&
                                            residentAccounts.data.length > 0 ? (
                                                residentAccounts.data.map((account) => {
                                                    const isSuspended =
                                                        account.locked_until &&
                                                        new Date(account.locked_until) > new Date();

                                                    return (
                                                        <tr
                                                            key={account.id}
                                                            className="transition-colors hover:bg-slate-50"
                                                        >
                                                            <td className="p-4">
                                                                <p className="text-sm font-bold text-slate-900 uppercase">
                                                                    {account.full_name}
                                                                </p>
                                                                <p className="text-[10px] font-medium text-slate-500">
                                                                    DOB:{' '}
                                                                    {new Date(
                                                                        account.date_of_birth
                                                                    ).toLocaleDateString()}
                                                                </p>
                                                            </td>
                                                            <td className="p-4">
                                                                <p className="font-mono text-xs font-bold text-slate-800">
                                                                    {account.contact_number}
                                                                </p>
                                                                <p className="text-[10px] text-slate-500">
                                                                    {account.email ||
                                                                        'Walang Email'}
                                                                </p>
                                                            </td>
                                                            <td className="p-4">
                                                                {isSuspended ? (
                                                                    <span className="rounded bg-red-100 px-2 py-1 text-[9px] font-black tracking-widest text-red-700 uppercase shadow-sm">
                                                                        Suspended
                                                                    </span>
                                                                ) : account.is_verified ? (
                                                                    <span className="rounded bg-green-100 px-2 py-1 text-[9px] font-black tracking-widest text-green-700 uppercase shadow-sm">
                                                                        Verified
                                                                    </span>
                                                                ) : (
                                                                    <span className="rounded bg-amber-100 px-2 py-1 text-[9px] font-black tracking-widest text-amber-700 uppercase shadow-sm">
                                                                        Unverified
                                                                    </span>
                                                                )}
                                                            </td>
                                                            <td className="flex justify-end gap-2 p-4 text-right">
                                                                {!account.is_verified && (
                                                                    <button
                                                                        type="button"
                                                                        onClick={(e) => { e.stopPropagation(); triggerManualVerify(account); }}
                                                                        className="rounded border border-green-200 bg-green-50 px-3 py-1.5 text-[9px] font-black tracking-widest text-green-600 uppercase shadow-sm transition-all hover:bg-green-100 active:scale-95"
                                                                    >
                                                                        Verify
                                                                    </button>
                                                                )}
                                                                <button
                                                                    onClick={(e) => { e.stopPropagation(); setSelectedAccount(account); setSuspendModalOpen(true); }}
                                                                    className="rounded border border-amber-200 bg-amber-50 px-3 py-1.5 text-[9px] font-black tracking-widest text-amber-600 uppercase shadow-sm transition-all hover:bg-amber-100 active:scale-95"
                                                                >
                                                                    Suspend
                                                                </button>
                                                                <button
                                                                    onClick={(e) => { e.stopPropagation(); setSelectedAccount(account); setDeleteAccountModalOpen(true); }}
                                                                    className="rounded border border-red-200 bg-red-50 px-3 py-1.5 text-[9px] font-black tracking-widest text-red-600 uppercase shadow-sm transition-all hover:bg-red-100 active:scale-95"
                                                                >
                                                                    Delete
                                                                </button>
                                                            </td>
                                                        </tr>
                                                    );
                                                })
                                            ) : (
                                                <tr>
                                                    <td
                                                        colSpan="4"
                                                        className="p-12 text-center font-bold text-slate-400 italic"
                                                    >
                                                        Walang nahanap na account.
                                                    </td>
                                                </tr>
                                            )}
                                        </tbody>
                                    </table>
                                    <div className="mt-4">
                                        <Pagination links={residentAccounts.links} />
                                    </div>
                                </div>
                            </div>
                        )}

                        {accountsSubTab === 'analytics' && (
                            <div className="animate-in fade-in p-6 duration-300">
                                {/* ACCOUNTS METRICS */}
                                <div className="mb-8 grid grid-cols-1 gap-4 sm:grid-cols-2">
                                    <div className="rounded-xl border border-blue-100 bg-blue-50 p-6 shadow-sm transition-all hover:-translate-y-1 hover:shadow-md">
                                        <p className="text-[10px] font-black tracking-widest text-blue-600 uppercase">
                                            Total Registered Accounts
                                        </p>
                                        <div className="mt-2 flex items-baseline gap-2">
                                            <p className="text-4xl font-black text-blue-700">
                                                {analyticsSummary?.total_registered || 0}
                                            </p>
                                            <p className="text-xs font-bold text-blue-500">
                                                active users
                                            </p>
                                        </div>
                                        <p className="mt-2 text-xs font-medium text-blue-600/80">
                                            Mga residenteng may online account sa platform.
                                        </p>
                                    </div>
                                    <div className="rounded-xl border border-slate-200 bg-white p-6 shadow-sm transition-all hover:-translate-y-1 hover:shadow-md">
                                        <p className="text-[10px] font-black tracking-widest text-slate-500 uppercase">
                                            Total Resident Masterlist
                                        </p>
                                        <div className="mt-2 flex items-baseline gap-2">
                                            <p className="text-4xl font-black text-slate-900">
                                                {analyticsSummary?.total_census || 0}
                                            </p>
                                            <p className="text-xs font-bold text-slate-500">
                                                total population
                                            </p>
                                        </div>
                                        <p className="mt-2 text-xs font-medium text-slate-500">
                                            Kabuuang bilang ng mga nakarehistro sa barangay
                                            masterlist.
                                        </p>
                                    </div>
                                </div>
                                <div className="flex flex-col items-center justify-between gap-4 rounded-xl border border-slate-200 bg-slate-50 p-6 sm:flex-row">
                                    <div>
                                        <h3 className="text-sm font-black tracking-tight text-slate-900 uppercase">
                                            Print Registered Accounts
                                        </h3>
                                        <p className="mt-1 text-xs text-slate-500">
                                            I-download bilang PDF ang listahan ng lahat ng
                                            registered accounts para sa record-keeping.
                                        </p>
                                    </div>
                                    <div className="flex shrink-0 gap-2">
                                        <a
                                            href={route('admin.analytics.print_accounts')}
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            className="flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-[10px] font-black tracking-widest text-slate-600 uppercase transition-all hover:bg-slate-100 active:scale-95"
                                        >
                                            <svg
                                                className="h-4 w-4"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    strokeLinecap="round"
                                                    strokeLinejoin="round"
                                                    strokeWidth="2"
                                                    d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                                                ></path>
                                            </svg>
                                            View PDF
                                        </a>
                                        <a
                                            href={
                                                route('admin.analytics.print_accounts') +
                                                '?download=1'
                                            }
                                            className="flex items-center gap-2 rounded-xl bg-slate-900 px-5 py-2.5 text-[10px] font-black tracking-widest text-white uppercase shadow-sm transition-all hover:bg-slate-800 active:scale-95"
                                        >
                                            <svg
                                                className="h-4 w-4"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    strokeLinecap="round"
                                                    strokeLinejoin="round"
                                                    strokeWidth="2"
                                                    d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"
                                                ></path>
                                            </svg>
                                            Download
                                        </a>
                                    </div>
                                </div>
                            </div>
                        )}

                        {accountsSubTab === 'pending' && (
                            <div className="animate-in fade-in p-6 duration-300">
                                <div className="mb-6">
                                    <h3 className="text-sm font-black tracking-tight text-slate-900 uppercase">
                                        Pending Registrations (Failed OCR)
                                    </h3>
                                    <p className="mt-1 text-xs text-slate-500">
                                        Mga residenteng hindi nakapasa sa automated ID verification. Paki-review nang manu-mano.
                                    </p>
                                </div>
                                
                                <div className="overflow-x-auto rounded-xl border border-slate-200">
                                    <table className="w-full border-collapse text-left">
                                        <thead>
                                            <tr className="border-b border-slate-200 bg-slate-100 text-[10px] tracking-[0.15em] text-slate-500 uppercase">
                                                <th className="p-4 font-black">Pangalan & OCR Status</th>
                                                <th className="p-4 font-black">ID Verification Data</th>
                                                <th className="p-4 text-right font-black">Aksyon</th>
                                            </tr>
                                        </thead>
                                        <tbody className="divide-y divide-slate-100 bg-white">
                                            {pendingUsers.length > 0 ? (
                                                pendingUsers.map((user) => (
                                                    <tr key={user.id} className="transition-colors hover:bg-slate-50">
                                                        <td className="p-4">
                                                            <p className="text-sm font-bold text-slate-900 uppercase">
                                                                {user.full_name}
                                                            </p>
                                                            <div className="mt-1 flex items-center gap-2">
                                                                <span className="rounded bg-red-100 px-2 py-1 text-[9px] font-black tracking-widest text-red-700 uppercase shadow-sm">
                                                                    Failed OCR ({user.ocr_attempts} attempts)
                                                                </span>
                                                            </div>
                                                        </td>
                                                        <td className="p-4">
                                                            <p className="font-mono text-xs font-bold text-slate-800">
                                                                DOB: {new Date(user.date_of_birth).toLocaleDateString()}
                                                            </p>
                                                            <p className="text-[10px] text-slate-500">
                                                                Contact: {user.contact_number}
                                                            </p>
                                                        </td>
                                                        <td className="flex justify-end gap-2 p-4 text-right">
                                                            <button
                                                                onClick={() => submitPendingManualVerify(user)}
                                                                className="rounded-lg border border-green-200 bg-green-50 px-4 py-2 text-[10px] font-black tracking-widest text-green-600 uppercase shadow-sm transition-all hover:bg-green-100 active:scale-95"
                                                            >
                                                                Manual Verify
                                                            </button>
                                                            <button
                                                                onClick={() => confirmRejectRegistration(user)}
                                                                className="rounded-lg border border-red-200 bg-red-50 px-4 py-2 text-[10px] font-black tracking-widest text-red-600 uppercase shadow-sm transition-all hover:bg-red-100 active:scale-95"
                                                            >
                                                                Reject & Delete
                                                            </button>
                                                        </td>
                                                    </tr>
                                                ))
                                            ) : (
                                                <tr>
                                                    <td colSpan="3" className="p-12 text-center font-bold text-slate-400 italic">
                                                        Walang pending registrations sa ngayon.
                                                    </td>
                                                </tr>
                                            )}
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        )}
                    </div>
                </div>
            )}

            {/* --- TAB 5: AUDIT LOGS --- */}
            {activeTab === 'audit' && (
                <div className="animate-in fade-in duration-500">
                    <div>
                        <div className="block overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                            <div className="flex flex-col items-start justify-between gap-4 border-b border-slate-200 bg-slate-50 p-6 sm:flex-row sm:items-center">
                                <h2 className="text-xl font-black tracking-tight text-slate-900 uppercase">
                                    System Audit Logs
                                </h2>
                                <form
                                    onSubmit={handleAuditSearch}
                                    className="flex w-full items-center gap-2 sm:w-auto"
                                >
                                    <input
                                        type="text"
                                        placeholder="I-search ang logs..."
                                        value={auditSearch}
                                        onChange={(e) => setAuditSearch(e.target.value)}
                                        className="w-full rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 outline-none focus:border-slate-500 focus:ring-2 focus:ring-slate-500 sm:w-64"
                                    />
                                    <button
                                        type="submit"
                                        className="rounded-xl bg-slate-900 px-4 py-2 text-sm font-black text-white shadow-sm transition-all hover:bg-slate-800"
                                    >
                                        Hanapin
                                    </button>
                                </form>
                            </div>
                            <div className="max-h-[600px] overflow-x-auto overflow-y-auto">
                                <table className="relative w-full border-collapse text-left">
                                    <thead className="sticky top-0 z-10">
                                        <tr className="border-b border-slate-200 bg-slate-100 text-[10px] tracking-[0.15em] text-slate-500 uppercase">
                                            <th className="p-4 font-black">Petsa & Oras</th>
                                            <th className="p-4 font-black">Admin</th>
                                            <th className="p-4 font-black">Aksyon</th>
                                            <th className="w-1/2 p-4 font-black">Detalye</th>
                                        </tr>
                                    </thead>
                                    <tbody className="divide-y divide-slate-100">
                                        {auditLogs.data && auditLogs.data.length > 0 ? (
                                            auditLogs.data.map((log) => (
                                                <tr
                                                    key={log.id}
                                                    className="transition-colors hover:bg-slate-50"
                                                >
                                                    <td className="p-4 text-xs font-bold text-slate-600">
                                                        {new Date(log.created_at).toLocaleString()}
                                                    </td>
                                                    <td className="p-4 text-xs font-bold text-slate-900 uppercase">
                                                        {log.admin?.first_name || 'System'}{' '}
                                                        {log.admin?.last_name || ''}
                                                    </td>
                                                    <td className="p-4">
                                                        <span className="rounded bg-slate-900 px-2 py-1 text-[9px] font-black tracking-widest text-white uppercase shadow-sm">
                                                            {log.action}
                                                        </span>
                                                    </td>
                                                    <td className="p-4 text-xs font-medium text-slate-600">
                                                        {log.description}
                                                    </td>
                                                </tr>
                                            ))
                                        ) : (
                                            <tr>
                                                <td
                                                    colSpan="4"
                                                    className="p-12 text-center font-bold text-slate-400 italic"
                                                >
                                                    Wala pang naitalang galaw sa system.
                                                </td>
                                            </tr>
                                        )}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <Pagination links={auditLogs.links} />
                    </div>
                </div>
            )}

            {/* --- TAB 5.5: LIVE ANALYTICS --- */}
            {activeTab === 'analytics' && (
                <div className="animate-in fade-in duration-500">
                    <div className="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                        <div className="flex flex-col items-start justify-between gap-4 border-b border-slate-200 bg-slate-50 p-6 sm:flex-row sm:items-center">
                            <h2 className="text-xl font-black tracking-tight text-slate-900 uppercase">
                                Live System Analytics
                            </h2>
                            {/* FALLBACK: PDF Generation Form */}
                            <form
                                method="POST"
                                action={route('admin.reports.generate')}
                                target="pdfViewerFrame"
                                onSubmit={submitGeneratePdf}
                                className="flex items-center gap-2"
                            >
                                <input
                                    type="hidden"
                                    name="_token"
                                    value={usePage().props.csrf_token}
                                />
                                <input type="hidden" name="report_month" value={reportMonth} />
                                <input type="hidden" name="report_year" value={reportYear} />
                                <button
                                    type="submit"
                                    className="flex items-center gap-2 rounded-xl bg-slate-900 px-5 py-2.5 text-[10px] font-black tracking-widest text-white uppercase shadow-sm transition-all hover:bg-slate-800 active:scale-95"
                                >
                                    <svg
                                        className="h-4 w-4"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            strokeLinecap="round"
                                            strokeLinejoin="round"
                                            strokeWidth="2"
                                            d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                                        ></path>
                                    </svg>
                                    I-Print as PDF
                                </button>
                            </form>
                        </div>

                        <div className="p-6">
                            {/* LIVE FILTERS */}
                            <div className="mb-8 flex flex-col items-end gap-4 rounded-xl border border-slate-100 bg-slate-50 p-5 sm:flex-row">
                                <div className="w-full sm:w-1/3">
                                    <label className="mb-2 block text-[10px] font-black tracking-widest text-slate-500 uppercase">
                                        Piliin ang Buwan
                                    </label>
                                    <select
                                        value={reportMonth}
                                        onChange={(e) => setReportMonth(e.target.value)}
                                        className="w-full cursor-pointer rounded-lg border border-slate-300 bg-white px-4 py-2.5 font-bold text-slate-700 outline-none focus:ring-2 focus:ring-red-600"
                                    >
                                        <option value="all">Buong Taon (All Months)</option>
                                        {[
                                            'January',
                                            'February',
                                            'March',
                                            'April',
                                            'May',
                                            'June',
                                            'July',
                                            'August',
                                            'September',
                                            'October',
                                            'November',
                                            'December',
                                        ].map((m, i) => (
                                            <option
                                                key={i}
                                                value={(i + 1).toString().padStart(2, '0')}
                                            >
                                                {m}
                                            </option>
                                        ))}
                                    </select>
                                </div>
                                <div className="w-full sm:w-1/3">
                                    <label className="mb-2 block text-[10px] font-black tracking-widest text-slate-500 uppercase">
                                        Piliin ang Taon
                                    </label>
                                    <select
                                        value={reportYear}
                                        onChange={(e) => setReportYear(e.target.value)}
                                        className="w-full cursor-pointer rounded-lg border border-slate-300 bg-white px-4 py-2.5 font-bold text-slate-700 outline-none focus:ring-2 focus:ring-red-600"
                                    >
                                        {Array.from(
                                            { length: new Date().getFullYear() - 2023 },
                                            (_, i) => new Date().getFullYear() - i
                                        ).map((y) => (
                                            <option key={y} value={y}>
                                                {y}
                                            </option>
                                        ))}
                                    </select>
                                </div>
                                <div className="w-full sm:w-1/3">
                                    <button
                                        onClick={() => {
                                            router.get(
                                                route('admin.dashboard'),
                                                {
                                                    analytics_month: reportMonth,
                                                    analytics_year: reportYear,
                                                    search: searchParam,
                                                    sort: sortParam,
                                                },
                                                {
                                                    preserveState: true,
                                                    preserveScroll: true,
                                                    only: ['analyticsSummary', 'filters'],
                                                }
                                            );
                                        }}
                                        className="w-full rounded-lg bg-red-600 px-6 py-3 text-xs font-black tracking-widest text-white uppercase shadow-md transition-all hover:bg-red-700 active:scale-95"
                                    >
                                        I-Filter ang Data
                                    </button>
                                </div>
                            </div>

                            {/* DASHBOARD STATS CARDS */}
                            <div className="mb-8 grid grid-cols-2 gap-4 lg:grid-cols-4">
                                <div className="rounded-xl border border-slate-100 bg-white p-5 text-center shadow-sm transition-all hover:-translate-y-1 hover:shadow-md">
                                    <p className="text-[10px] font-black tracking-widest text-slate-400 uppercase">
                                        Total Requests
                                    </p>
                                    <p className="mt-2 text-4xl font-black text-slate-900">
                                        {analyticsSummary?.total || 0}
                                    </p>
                                </div>
                                <div className="rounded-xl border border-green-100 bg-green-50 p-5 text-center shadow-sm transition-all hover:-translate-y-1 hover:shadow-md">
                                    <p className="text-[10px] font-black tracking-widest text-green-600 uppercase">
                                        Released
                                    </p>
                                    <p className="mt-2 text-4xl font-black text-green-700">
                                        {analyticsSummary?.released || 0}
                                    </p>
                                </div>
                                <div className="rounded-xl border border-blue-100 bg-blue-50 p-5 text-center shadow-sm transition-all hover:-translate-y-1 hover:shadow-md">
                                    <p className="text-[10px] font-black tracking-widest text-blue-600 uppercase">
                                        Processing
                                    </p>
                                    <p className="mt-2 text-4xl font-black text-blue-700">
                                        {analyticsSummary?.processing || 0}
                                    </p>
                                </div>
                                <div className="rounded-xl border border-red-100 bg-red-50 p-5 text-center shadow-sm transition-all hover:-translate-y-1 hover:shadow-md">
                                    <p className="text-[10px] font-black tracking-widest text-red-600 uppercase">
                                        Rejected / Canceled
                                    </p>
                                    <p className="mt-2 text-4xl font-black text-red-700">
                                        {analyticsSummary?.rejected || 0}
                                    </p>
                                </div>
                            </div>

                            {/* RECHARTS GRAPHS */}
                            <div className="grid grid-cols-1 gap-8 lg:grid-cols-2">
                                {/* Bar Chart: Online vs Walkin */}
                                <div className="rounded-xl border border-slate-100 bg-white p-6 shadow-sm">
                                    <h3 className="mb-6 text-center text-xs font-black tracking-widest text-slate-500 uppercase">
                                        Service Channel Usage
                                    </h3>
                                    <div className="h-64 w-full">
                                        <ResponsiveContainer width="100%" height="100%">
                                            <BarChart
                                                data={[
                                                    {
                                                        name: 'Online',
                                                        count: analyticsSummary?.online || 0,
                                                    },
                                                    {
                                                        name: 'Walk-in',
                                                        count: analyticsSummary?.walkin || 0,
                                                    },
                                                ]}
                                                margin={{ top: 10, right: 30, left: 0, bottom: 0 }}
                                            >
                                                <CartesianGrid
                                                    strokeDasharray="3 3"
                                                    vertical={false}
                                                    stroke="#e2e8f0"
                                                />
                                                <XAxis
                                                    dataKey="name"
                                                    tick={{
                                                        fontSize: 12,
                                                        fill: '#64748b',
                                                        fontWeight: 'bold',
                                                    }}
                                                    axisLine={false}
                                                    tickLine={false}
                                                />
                                                <YAxis
                                                    allowDecimals={false}
                                                    tick={{ fontSize: 12, fill: '#64748b' }}
                                                    axisLine={false}
                                                    tickLine={false}
                                                />
                                                <Tooltip
                                                    cursor={{ fill: '#f8fafc' }}
                                                    contentStyle={{
                                                        borderRadius: '12px',
                                                        border: 'none',
                                                        boxShadow:
                                                            '0 4px 6px -1px rgb(0 0 0 / 0.1)',
                                                        fontWeight: 'bold',
                                                    }}
                                                />
                                                <Bar dataKey="count" radius={[1]} maxBarSize={60}>
                                                    {[
                                                        {
                                                            name: 'Online',
                                                            count: analyticsSummary?.online || 0,
                                                        },
                                                        {
                                                            name: 'Walk-in',
                                                            count: analyticsSummary?.walkin || 0,
                                                        },
                                                    ].map((entry, index) => (
                                                        <Cell
                                                            key={`cell-${index}`}
                                                            fill={
                                                                index === 0 ? '#ef4444' : '#0f172a'
                                                            }
                                                        />
                                                    ))}
                                                </Bar>
                                            </BarChart>
                                        </ResponsiveContainer>
                                    </div>
                                </div>

                                {/* Pie Chart: Status Distribution */}
                                <div className="rounded-xl border border-slate-100 bg-white p-6 shadow-sm">
                                    <h3 className="mb-6 text-center text-xs font-black tracking-widest text-slate-500 uppercase">
                                        Document Status Distribution
                                    </h3>
                                    <div className="h-64 w-full">
                                        <ResponsiveContainer width="100%" height="100%">
                                            <PieChart>
                                                <Pie
                                                    data={[
                                                        {
                                                            name: 'Pending',
                                                            value: analyticsSummary?.pending || 0,
                                                            color: '#f59e0b',
                                                        },
                                                        {
                                                            name: 'Processing',
                                                            value:
                                                                analyticsSummary?.processing || 0,
                                                            color: '#3b82f6',
                                                        },
                                                        {
                                                            name: 'Released',
                                                            value: analyticsSummary?.released || 0,
                                                            color: '#22c55e',
                                                        },
                                                        {
                                                            name: 'Rejected',
                                                            value: analyticsSummary?.rejected || 0,
                                                            color: '#ef4444',
                                                        },
                                                    ].filter((d) => d.value > 0)}
                                                    cx="50%"
                                                    cy="50%"
                                                    innerRadius={65}
                                                    outerRadius={90}
                                                    paddingAngle={5}
                                                    dataKey="value"
                                                    stroke="none"
                                                >
                                                    {[
                                                        {
                                                            name: 'Pending',
                                                            value: analyticsSummary?.pending || 0,
                                                            color: '#f59e0b',
                                                        },
                                                        {
                                                            name: 'Processing',
                                                            value:
                                                                analyticsSummary?.processing || 0,
                                                            color: '#3b82f6',
                                                        },
                                                        {
                                                            name: 'Released',
                                                            value: analyticsSummary?.released || 0,
                                                            color: '#22c55e',
                                                        },
                                                        {
                                                            name: 'Rejected',
                                                            value: analyticsSummary?.rejected || 0,
                                                            color: '#ef4444',
                                                        },
                                                    ]
                                                        .filter((d) => d.value > 0)
                                                        .map((entry, index) => (
                                                            <Cell
                                                                key={`cell-${index}`}
                                                                fill={entry.color}
                                                            />
                                                        ))}
                                                </Pie>
                                                <Tooltip
                                                    contentStyle={{
                                                        borderRadius: '12px',
                                                        border: 'none',
                                                        boxShadow:
                                                            '0 4px 6px -1px rgb(0 0 0 / 0.1)',
                                                        fontWeight: 'bold',
                                                    }}
                                                />
                                                <Legend
                                                    iconType="circle"
                                                    wrapperStyle={{
                                                        fontSize: '12px',
                                                        fontWeight: 'bold',
                                                        paddingTop: '20px',
                                                    }}
                                                />
                                            </PieChart>
                                        </ResponsiveContainer>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            )}
            {/* --- TAB 5.8: MANAGE DOCUMENTS --- */}
            {activeTab === 'documents' && (
                <div className="animate-in fade-in duration-500">
                    <div className="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                        <div className="flex flex-col items-start justify-between gap-4 border-b border-slate-200 bg-slate-50 p-6 sm:flex-row sm:items-center">
                            <div>
                                <h2 className="text-xl font-black tracking-tight text-slate-900 uppercase">
                                    Document Management
                                </h2>
                                <p className="text-sm text-slate-500">
                                    Kontrolin ang presyo, processing time, at requirements ng mga
                                    dokumento.
                                </p>
                            </div>
                            <button
                                onClick={() => openDocModal('add')}
                                className="rounded-xl bg-slate-900 px-5 py-2.5 text-xs font-black tracking-widest text-white uppercase shadow-sm transition-all hover:bg-slate-800 active:scale-95"
                            >
                                + Add New Document
                            </button>
                        </div>
                        <div className="overflow-x-auto">
                            <table className="w-full border-collapse text-left">
                                <thead>
                                    <tr className="border-b border-slate-200 bg-slate-100 text-[10px] tracking-[0.15em] text-slate-500 uppercase">
                                        <th className="p-4 font-black">Document Name & Reqs</th>
                                        <th className="p-4 font-black">Fee & Time</th>
                                        <th className="p-4 font-black">Status</th>
                                        <th className="p-4 text-right font-black">Actions</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-slate-100">
                                    {documents.length > 0 ? (
                                        documents.map((doc) => (
                                            <tr
                                                key={doc.id}
                                                className="transition-colors hover:bg-slate-50"
                                            >
                                                <td className="p-4">
                                                    <p
                                                        className={`text-sm font-bold uppercase ${doc.is_active ? 'text-slate-900' : 'text-slate-400'}`}
                                                    >
                                                        {doc.name}
                                                    </p>
                                                    <p className="mt-1 max-w-xs truncate text-[10px] text-slate-500">
                                                        {doc.requirements_description}
                                                    </p>
                                                </td>
                                                <td className="p-4">
                                                    <p className="text-xs font-bold text-slate-700">
                                                        ₱{doc.processing_fee}
                                                    </p>
                                                    <p className="text-[10px] font-medium text-slate-500">
                                                        {doc.processing_time_minutes} mins avg
                                                    </p>
                                                </td>
                                                <td className="p-4">
                                                    <span
                                                        className={`rounded-md px-2 py-1 text-[10px] font-black tracking-widest uppercase shadow-sm ${doc.is_active ? 'border border-green-200 bg-green-100 text-green-700' : 'border border-slate-200 bg-slate-100 text-slate-500'}`}
                                                    >
                                                        {doc.is_active ? 'Active' : 'Inactive'}
                                                    </span>
                                                </td>
                                                <td className="flex justify-end gap-2 p-4 text-right">
                                                    <button
                                                        onClick={() => openDocModal('edit', doc)}
                                                        className="rounded-lg bg-blue-50 px-3 py-1.5 text-[10px] font-black text-blue-600 uppercase transition-all hover:bg-blue-100 active:scale-95"
                                                    >
                                                        Edit
                                                    </button>
                                                    <button
                                                        onClick={() => toggleDocStatus(doc.id)}
                                                        className={`rounded-lg px-3 py-1.5 text-[10px] font-black uppercase transition-all active:scale-95 ${doc.is_active ? 'bg-red-50 text-red-600 hover:bg-red-100' : 'bg-green-50 text-green-600 hover:bg-green-100'}`}
                                                    >
                                                        {doc.is_active ? 'Deactivate' : 'Activate'}
                                                    </button>
                                                </td>
                                            </tr>
                                        ))
                                    ) : (
                                        <tr>
                                            <td
                                                colSpan="4"
                                                className="p-12 text-center font-bold text-slate-400 italic"
                                            >
                                                Walang nakarehistrong dokumento.
                                            </td>
                                        </tr>
                                    )}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            )}
            {/* --- TAB 7: RESIDENT MASTERLIST (KYC CENSUS) --- */}
            {activeTab === 'residents' && (
                <div className="animate-in fade-in duration-500">
                    <div className="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                        {/* HEADER & ACTION BUTTONS */}
                        <div className="flex flex-col items-start justify-between gap-4 border-b border-slate-200 bg-slate-50 p-6 sm:flex-row sm:items-center">
                            <div>
                                <h2 className="text-xl font-black tracking-tight text-slate-900 uppercase">
                                    Resident Masterlist
                                </h2>
                                <p className="text-sm text-slate-500">
                                    Pamahalaan ang opisyal na listahan ng mga residente para sa
                                    Automated ID scanner.
                                </p>
                            </div>
                            <div className="flex w-full flex-col gap-2 sm:w-auto sm:flex-row">
                                <button
                                    onClick={() => setImportModalOpen(true)}
                                    className="flex items-center justify-center gap-2 rounded-xl bg-blue-50 px-5 py-2.5 text-xs font-black tracking-widest text-blue-700 uppercase shadow-sm transition-all hover:bg-blue-100 active:scale-95"
                                >
                                    <svg
                                        className="h-4 w-4"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            strokeLinecap="round"
                                            strokeLinejoin="round"
                                            strokeWidth="2"
                                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"
                                        ></path>
                                    </svg>
                                    Upload CSV/Excel
                                </button>
                                <button
                                    onClick={() => openResidentModal('add')}
                                    className="flex items-center justify-center gap-2 rounded-xl bg-slate-900 px-5 py-2.5 text-xs font-black tracking-widest text-white uppercase shadow-sm transition-all hover:bg-slate-800 active:scale-95"
                                >
                                    <svg
                                        className="h-4 w-4"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            strokeLinecap="round"
                                            strokeLinejoin="round"
                                            strokeWidth="2"
                                            d="M12 4v16m8-8H4"
                                        ></path>
                                    </svg>
                                    Add Resident
                                </button>
                            </div>
                        </div>

                        {/* SEARCH BAR */}
                        <div className="border-b border-slate-100 bg-white p-4">
                            <input
                                type="text"
                                placeholder="I-search ang pangalan ng residente..."
                                value={residentSearch}
                                onChange={(e) => setResidentSearch(e.target.value)}
                                className="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm font-medium text-slate-700 outline-none focus:border-slate-500 focus:ring-2 focus:ring-slate-500 sm:max-w-md"
                            />
                        </div>

                        {/* THE MASTERLIST TABLE */}
                        <div className="overflow-x-auto">
                            <table className="w-full border-collapse text-left">
                                <thead>
                                    <tr className="border-b border-slate-200 bg-slate-100 text-[10px] tracking-[0.15em] text-slate-500 uppercase">
                                        <th className="w-12 p-4 text-center">
                                            <input
                                                type="checkbox"
                                                className="h-4 w-4 rounded border-slate-300 text-slate-900 focus:ring-slate-900"
                                                onChange={handleResidentSelectAll}
                                                checked={
                                                    censusRecords.data &&
                                                    censusRecords.data.length > 0 &&
                                                    selectedResidents.length ===
                                                        censusRecords.data.length
                                                }
                                            />
                                        </th>
                                        <th className="p-4 font-black">Pangalan</th>
                                        <th className="p-4 font-black">Kasarian & Edad</th>
                                        <th className="p-4 font-black">Tirahan</th>
                                        <th className="p-4 text-right font-black">Aksyon</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-slate-100">
                                    {renderedMasterlist}
                                </tbody>
                            </table>
                        </div>
                        <div className="mt-6 flex justify-center pb-8">
                            <Pagination links={censusRecords.links} />
                        </div>
                    </div>
                </div>
            )}
            {/* --- TAB 6: SETTINGS (PHASE 5 COMPLETED) --- */}
            {activeTab === 'settings' && (
                <div className="animate-in fade-in duration-500">
                    <div className="mx-auto mt-8 max-w-2xl">
                        <div className="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm md:p-8">
                            <div className="mb-6 flex items-center gap-3 border-b border-slate-100 pb-4">
                                <div className="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-900 text-white shadow-sm">
                                    <svg
                                        className="h-5 w-5"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            strokeLinecap="round"
                                            strokeLinejoin="round"
                                            strokeWidth="2"
                                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"
                                        ></path>
                                    </svg>
                                </div>
                                <h2 className="text-2xl font-black tracking-tight text-slate-900 uppercase">
                                    Security Settings
                                </h2>
                            </div>
                            <p className="mb-6 text-sm font-medium text-slate-500">
                                Mahalaga: Palitan agad ang iyong default password upang maiwasan ang
                                unauthorized access sa Admin Portal.
                            </p>

                            <form onSubmit={submitPasswordUpdate} className="space-y-5">
                                <div>
                                    <label className="mb-2 block text-[10px] font-black tracking-widest text-slate-400 uppercase">
                                        Kasalukuyang Password{' '}
                                        <span className="text-red-500">*</span>
                                    </label>
                                    <input
                                        type="password"
                                        value={passwordForm.data.current_password}
                                        onChange={(e) =>
                                            passwordForm.setData('current_password', e.target.value)
                                        }
                                        required
                                        className={`w-full rounded-xl border bg-slate-50 px-4 py-3 font-bold text-slate-700 focus:ring-2 focus:ring-slate-900 focus:outline-none ${passwordForm.errors.current_password ? 'border-red-500 ring-1 ring-red-500' : 'border-slate-300'}`}
                                    />
                                    {passwordForm.errors.current_password && (
                                        <p className="mt-1 text-xs text-red-500">
                                            {passwordForm.errors.current_password}
                                        </p>
                                    )}
                                </div>
                                <div className="grid grid-cols-1 gap-5 sm:grid-cols-2">
                                    <div>
                                        <label className="mb-2 block text-[10px] font-black tracking-widest text-slate-400 uppercase">
                                            Bagong Password <span className="text-red-500">*</span>
                                        </label>
                                        <input
                                            type="password"
                                            value={passwordForm.data.password}
                                            onChange={(e) =>
                                                passwordForm.setData('password', e.target.value)
                                            }
                                            required
                                            minLength="8"
                                            className={`w-full rounded-xl border bg-slate-50 px-4 py-3 font-bold text-slate-700 focus:ring-2 focus:ring-slate-900 focus:outline-none ${passwordForm.errors.password ? 'border-red-500 ring-1 ring-red-500' : 'border-slate-300'}`}
                                        />
                                        {passwordForm.errors.password && (
                                            <p className="mt-1 text-xs text-red-500">
                                                {passwordForm.errors.password}
                                            </p>
                                        )}
                                    </div>
                                    <div>
                                        <label className="mb-2 block text-[10px] font-black tracking-widest text-slate-400 uppercase">
                                            I-type Ulit (Confirm){' '}
                                            <span className="text-red-500">*</span>
                                        </label>
                                        <input
                                            type="password"
                                            value={passwordForm.data.password_confirmation}
                                            onChange={(e) =>
                                                passwordForm.setData(
                                                    'password_confirmation',
                                                    e.target.value
                                                )
                                            }
                                            required
                                            minLength="8"
                                            className={`w-full rounded-xl border bg-slate-50 px-4 py-3 font-bold text-slate-700 focus:ring-2 focus:ring-slate-900 focus:outline-none ${passwordForm.errors.password_confirmation ? 'border-red-500 ring-1 ring-red-500' : 'border-slate-300'}`}
                                        />
                                    </div>
                                </div>
                                <div className="flex justify-end border-t border-slate-100 pt-4">
                                    <button
                                        type="submit"
                                        disabled={passwordForm.processing}
                                        className="flex items-center gap-2 rounded-xl bg-slate-900 px-8 py-3.5 text-xs font-black tracking-widest text-white uppercase shadow-md transition-all hover:bg-slate-800 active:scale-95 disabled:opacity-50"
                                    >
                                        <svg
                                            className="h-4 w-4"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                strokeLinecap="round"
                                                strokeLinejoin="round"
                                                strokeWidth="2"
                                                d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"
                                            ></path>
                                        </svg>
                                        {passwordForm.processing
                                            ? 'Sinasave...'
                                            : 'I-save ang Bagong Password'}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            )}

            {/* --- MODALS --- */}

            {/* Suspend Account Modal */}
            {suspendModalOpen && selectedAccount && (
                <div className="fixed inset-0 z-[200] flex items-center justify-center bg-slate-900/80 p-4 backdrop-blur-sm transition-opacity">
                    <div className="w-full max-w-sm transform overflow-hidden rounded-2xl border border-amber-100 bg-white p-6 shadow-2xl transition-all">
                        <div className="mb-6 flex flex-col items-center text-center">
                            <div className="mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-amber-100 text-amber-600">
                                <svg
                                    className="h-8 w-8"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        strokeLinecap="round"
                                        strokeLinejoin="round"
                                        strokeWidth="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                                    ></path>
                                </svg>
                            </div>
                            <h3 className="text-xl font-black tracking-tight text-slate-900 uppercase">
                                Suspend Account?
                            </h3>
                            <p className="mt-2 px-4 text-sm font-medium text-slate-500">
                                Sigurado ka bang gusto mong patawan ng 7-araw na penalty si{' '}
                                <strong className="text-slate-900">
                                    {selectedAccount.full_name}
                                </strong>
                                ?
                            </p>
                        </div>
                        <div className="flex flex-col gap-3">
                            <button
                                onClick={() => setSuspendModalOpen(false)}
                                className="w-full rounded-xl border border-slate-200 bg-white py-3 text-[10px] font-black tracking-widest text-slate-500 uppercase transition-all hover:bg-slate-50"
                            >
                                Cancel
                            </button>
                            <button
                                onClick={submitSuspend}
                                className="w-full rounded-xl bg-amber-600 py-3 text-xs font-black tracking-widest text-white uppercase shadow-md transition-all hover:bg-amber-700 active:scale-95"
                            >
                                Ipataw ang Penalty
                            </button>
                        </div>
                    </div>
                </div>
            )}

            {/* Delete Account Modal */}
            {deleteAccountModalOpen && selectedAccount && (
                <div className="fixed inset-0 z-[200] flex items-center justify-center bg-slate-900/80 p-4 backdrop-blur-sm transition-opacity">
                    <div className="w-full max-w-sm transform overflow-hidden rounded-2xl border border-red-100 bg-white p-6 shadow-2xl transition-all">
                        <div className="mb-6 flex flex-col items-center text-center">
                            <div className="mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-red-100 text-red-600">
                                <svg
                                    className="h-8 w-8"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        strokeLinecap="round"
                                        strokeLinejoin="round"
                                        strokeWidth="2"
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                                    ></path>
                                </svg>
                            </div>
                            <h3 className="text-xl font-black tracking-tight text-slate-900 uppercase">
                                Delete Account?
                            </h3>
                            <p className="mb-6 text-sm text-slate-500">
                                Sigurado ka bang gusto mong permanenteng burahin ang account ni{' '}
                                <strong className="text-slate-900 uppercase">
                                    {selectedAccount.full_name}
                                </strong>
                                ? Hindi na maibabalik ang impormasyong ito.
                            </p>
                        </div>
                        <div className="flex flex-col gap-3">
                            <button
                                onClick={() => setDeleteAccountModalOpen(false)}
                                className="w-full rounded-xl border border-slate-200 bg-white py-3 text-[10px] font-black tracking-widest text-slate-500 uppercase transition-all hover:bg-slate-50"
                            >
                                Cancel
                            </button>
                            <button
                                onClick={submitDelete}
                                className="w-full rounded-xl bg-red-600 py-3 text-xs font-black tracking-widest text-white uppercase shadow-md transition-all hover:bg-red-700 active:scale-95"
                            >
                                Delete Permanently
                            </button>
                        </div>
                    </div>
                </div>
            )}

            {/* Resident Masterlist Add/Edit Modal */}
            {residentModalOpen && (
                <div className="fixed inset-0 z-[200] flex items-center justify-center bg-slate-900/80 p-4 backdrop-blur-sm transition-opacity">
                    <div className="w-full max-w-2xl transform overflow-hidden rounded-2xl border border-slate-100 bg-white p-6 shadow-2xl transition-all md:p-8">
                        <div className="mb-6 flex items-center gap-3">
                            <h3 className="text-xl font-black tracking-tight text-slate-900 uppercase">
                                Add / Edit Resident
                            </h3>
                        </div>
                        <form onSubmit={submitResident}>
                            <div className="grid gap-4 md:grid-cols-2">
                                <div>
                                    <label className="mb-1 block text-[10px] font-black tracking-widest text-slate-400 uppercase">
                                        First Name
                                    </label>
                                    <input
                                        type="text"
                                        value={residentForm.data.first_name}
                                        onChange={(e) =>
                                            residentForm.setData('first_name', e.target.value)
                                        }
                                        className="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm font-medium outline-none focus:border-slate-500 focus:ring-2 focus:ring-slate-500"
                                    />
                                    {residentForm.errors.first_name && (
                                        <div className="mt-1 text-xs text-red-500">
                                            {residentForm.errors.first_name}
                                        </div>
                                    )}
                                </div>
                                <div>
                                    <label className="mb-1 block text-[10px] font-black tracking-widest text-slate-400 uppercase">
                                        Middle Name
                                    </label>
                                    <input
                                        type="text"
                                        value={residentForm.data.middle_name}
                                        onChange={(e) =>
                                            residentForm.setData('middle_name', e.target.value)
                                        }
                                        className="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm font-medium outline-none focus:border-slate-500 focus:ring-2 focus:ring-slate-500"
                                    />
                                    {residentForm.errors.middle_name && (
                                        <div className="mt-1 text-xs text-red-500">
                                            {residentForm.errors.middle_name}
                                        </div>
                                    )}
                                </div>
                                <div>
                                    <label className="mb-1 block text-[10px] font-black tracking-widest text-slate-400 uppercase">
                                        Last Name
                                    </label>
                                    <input
                                        type="text"
                                        value={residentForm.data.last_name}
                                        onChange={(e) =>
                                            residentForm.setData('last_name', e.target.value)
                                        }
                                        className="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm font-medium outline-none focus:border-slate-500 focus:ring-2 focus:ring-slate-500"
                                    />
                                    {residentForm.errors.last_name && (
                                        <div className="mt-1 text-xs text-red-500">
                                            {residentForm.errors.last_name}
                                        </div>
                                    )}
                                </div>
                                <div>
                                    <label className="mb-1 block text-[10px] font-black tracking-widest text-slate-400 uppercase">
                                        Suffix
                                    </label>
                                    <input
                                        type="text"
                                        value={residentForm.data.suffix}
                                        onChange={(e) =>
                                            residentForm.setData('suffix', e.target.value)
                                        }
                                        className="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm font-medium outline-none focus:border-slate-500 focus:ring-2 focus:ring-slate-500"
                                        placeholder="e.g. Jr., Sr., III"
                                    />
                                    {residentForm.errors.suffix && (
                                        <div className="mt-1 text-xs text-red-500">
                                            {residentForm.errors.suffix}
                                        </div>
                                    )}
                                </div>
                                <div>
                                    <label className="mb-1 block text-[10px] font-black tracking-widest text-slate-400 uppercase">
                                        Date of Birth
                                    </label>
                                    <input
                                        type="date"
                                        value={residentForm.data.date_of_birth}
                                        onChange={(e) =>
                                            residentForm.setData('date_of_birth', e.target.value)
                                        }
                                        className="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm font-medium outline-none focus:border-slate-500 focus:ring-2 focus:ring-slate-500"
                                    />
                                    {residentForm.errors.date_of_birth && (
                                        <div className="mt-1 text-xs text-red-500">
                                            {residentForm.errors.date_of_birth}
                                        </div>
                                    )}
                                </div>
                                <div>
                                    <label className="mb-1 block text-[10px] font-black tracking-widest text-slate-400 uppercase">
                                        Sex
                                    </label>
                                    <select
                                        value={residentForm.data.sex}
                                        onChange={(e) =>
                                            residentForm.setData('sex', e.target.value)
                                        }
                                        className="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm font-medium outline-none focus:border-slate-500 focus:ring-2 focus:ring-slate-500"
                                    >
                                        <option value="">Select Sex...</option>
                                        <option value="Male">Male</option>
                                        <option value="Female">Female</option>
                                    </select>
                                    {residentForm.errors.sex && (
                                        <div className="mt-1 text-xs text-red-500">
                                            {residentForm.errors.sex}
                                        </div>
                                    )}
                                </div>
                                <div className="md:col-span-2">
                                    <label className="mb-1 block text-[10px] font-black tracking-widest text-slate-400 uppercase">
                                        Address
                                    </label>
                                    <input
                                        type="text"
                                        value={residentForm.data.address}
                                        onChange={(e) =>
                                            residentForm.setData('address', e.target.value)
                                        }
                                        className="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm font-medium outline-none focus:border-slate-500 focus:ring-2 focus:ring-slate-500"
                                    />
                                    {residentForm.errors.address && (
                                        <div className="mt-1 text-xs text-red-500">
                                            {residentForm.errors.address}
                                        </div>
                                    )}
                                </div>
                            </div>
                            <div className="mt-8 flex justify-end gap-3 border-t border-slate-100 pt-4">
                                <button
                                    type="button"
                                    onClick={() => setResidentModalOpen(false)}
                                    className="rounded-lg px-4 py-2.5 text-xs font-bold text-slate-600 transition-all hover:bg-slate-100 active:scale-95"
                                >
                                    Cancel
                                </button>
                                <button
                                    type="submit"
                                    disabled={residentForm.processing}
                                    className="rounded-lg bg-slate-900 px-6 py-2.5 text-xs font-black tracking-widest text-white uppercase shadow-sm transition-all hover:bg-slate-800 active:scale-95 disabled:opacity-50"
                                >
                                    {residentForm.processing ? 'Saving...' : 'Save Resident'}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}

            {/* Smart Importer Modal */}
            {importModalOpen && (
                <div className="fixed inset-0 z-[200] flex items-center justify-center bg-slate-900/80 p-4 backdrop-blur-sm transition-opacity">
                    <div className="w-full max-w-lg transform overflow-hidden rounded-2xl border border-slate-100 bg-white p-6 shadow-2xl transition-all">
                        <h3 className="mb-4 text-xl font-black tracking-tight text-slate-900 uppercase">
                            Upload CSV Records
                        </h3>

                        <div className="mb-6 rounded-lg border-l-4 border-blue-500 bg-blue-50 p-4 shadow-sm">
                            <p className="text-sm font-bold text-blue-800">
                                Paalala: Smart Upsert Logic
                            </p>
                            <p className="mt-1 text-xs font-medium text-blue-700">
                                Kung ang Pangalan at Petsa ng Kapanganakan ay nasa database na,
                                ia-update lamang nito ang record at hindi gagawa ng duplicate.
                            </p>
                        </div>

                        <div className="mb-6 flex justify-center">
                            <a
                                href={route('admin.census.template')}
                                download
                                className="flex items-center gap-2 rounded-lg bg-slate-100 px-4 py-2 text-[10px] font-black tracking-widest text-slate-700 uppercase transition-all hover:bg-slate-200 active:scale-95"
                            >
                                <svg
                                    className="h-4 w-4"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        strokeLinecap="round"
                                        strokeLinejoin="round"
                                        strokeWidth="2"
                                        d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"
                                    ></path>
                                </svg>
                                Download CSV Template
                            </a>
                        </div>

                        <form onSubmit={submitImport} className="space-y-4">
                            <div>
                                <label className="mb-2 block text-[10px] font-black tracking-widest text-slate-400 uppercase">
                                    Piliin ang CSV File
                                </label>
                                <input
                                    type="file"
                                    accept=".csv"
                                    onChange={(e) =>
                                        importForm.setData('import_file', e.target.files[0])
                                    }
                                    required
                                    className="w-full cursor-pointer rounded-lg border border-slate-300 bg-slate-50 px-3 py-2 text-sm text-slate-700 file:mr-4 file:cursor-pointer file:rounded-md file:border-0 file:bg-blue-100 file:px-4 file:py-2 file:text-xs file:font-bold file:text-blue-700 hover:file:bg-blue-200"
                                />
                                {importForm.errors.import_file && (
                                    <p className="mt-1 text-xs font-bold text-red-500">
                                        {importForm.errors.import_file}
                                    </p>
                                )}
                            </div>

                            <div className="mt-6 flex justify-end gap-3 border-t border-slate-100 pt-4">
                                <button
                                    type="button"
                                    onClick={() => {
                                        setImportModalOpen(false);
                                        importForm.reset();
                                    }}
                                    className="rounded-xl bg-slate-200 px-5 py-2.5 text-xs font-black tracking-widest text-slate-700 uppercase transition-all hover:bg-slate-300 active:scale-95"
                                >
                                    Cancel
                                </button>
                                <button
                                    type="submit"
                                    disabled={importForm.processing}
                                    className="flex items-center gap-2 rounded-xl bg-slate-900 px-6 py-2.5 text-xs font-black tracking-widest text-white uppercase shadow-md transition-all hover:bg-slate-800 active:scale-95 disabled:opacity-50"
                                >
                                    {importForm.processing ? 'Uploading...' : 'Upload Records'}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}

            {/* Batch Action Modal */}
            {batchModal.isOpen && (
                <div className="fixed inset-0 z-[200] flex items-center justify-center bg-slate-900/80 p-4 backdrop-blur-sm transition-opacity">
                    <div className="w-full max-w-sm transform overflow-hidden rounded-2xl border border-slate-100 bg-white p-6 shadow-2xl transition-all">
                        <div className="mb-4 flex items-center gap-3">
                            <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-slate-100 text-slate-900">
                                <svg
                                    className="h-5 w-5"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        strokeLinecap="round"
                                        strokeLinejoin="round"
                                        strokeWidth="2"
                                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                    ></path>
                                </svg>
                            </div>
                            <h3 className="text-lg font-black tracking-tight text-slate-900 uppercase">
                                Kumpirmahin ang Batch Action
                            </h3>
                        </div>
                        <p className="text-sm font-medium text-slate-600">
                            Mayroong{' '}
                            <strong className="text-slate-900">
                                {batchModal.specificIds.length}
                            </strong>{' '}
                            eligible request(s). Sigurado ka bang gusto mong i-update ang status
                            nila papuntang{' '}
                            <strong className="text-slate-900 uppercase">
                                {batchModal.nextStatus.replace('_', ' ')}
                            </strong>
                            ?
                        </p>
                        <div className="mt-6 flex justify-end gap-3 border-t border-slate-100 pt-4">
                            <button
                                onClick={() =>
                                    setBatchModal({
                                        isOpen: false,
                                        nextStatus: '',
                                        specificIds: [],
                                    })
                                }
                                className="rounded-lg px-4 py-2.5 text-xs font-bold text-slate-600 transition-all hover:bg-slate-100 active:scale-95"
                            >
                                Kanselahin
                            </button>
                            <button
                                onClick={confirmBatchAction}
                                className="rounded-lg bg-slate-900 px-6 py-2.5 text-xs font-black tracking-widest text-white uppercase shadow-sm transition-all hover:bg-slate-800 active:scale-95"
                            >
                                Oo, I-proseso
                            </button>
                        </div>
                    </div>
                </div>
            )}

            {/* Document Management Modal */}
            {docModal.isOpen && (
                <div className="fixed inset-0 z-[200] flex items-center justify-center bg-slate-900/80 p-4 backdrop-blur-sm transition-opacity">
                    <div className="w-full max-w-lg transform overflow-hidden rounded-2xl border border-slate-100 bg-white p-6 shadow-2xl transition-all">
                        <h3 className="mb-4 text-xl font-black tracking-tight text-slate-900 uppercase">
                            {docModal.mode === 'add' ? 'Add New Document' : 'Edit Document'}
                        </h3>
                        <form onSubmit={submitDoc} className="space-y-4">
                            <div>
                                <label className="mb-1 block text-[10px] font-black tracking-widest text-slate-400 uppercase">
                                    Document Name
                                </label>
                                <input
                                    type="text"
                                    value={docForm.data.name}
                                    onChange={(e) => docForm.setData('name', e.target.value)}
                                    required
                                    className="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-bold text-slate-800 outline-none focus:ring-2 focus:ring-slate-900"
                                />
                            </div>
                            <div>
                                <label className="mb-1 block text-[10px] font-black tracking-widest text-slate-400 uppercase">
                                    Requirements (Comma separated)
                                </label>
                                <textarea
                                    value={docForm.data.requirements_description}
                                    onChange={(e) =>
                                        docForm.setData('requirements_description', e.target.value)
                                    }
                                    required
                                    rows="3"
                                    className="w-full resize-none rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-medium text-slate-800 outline-none focus:ring-2 focus:ring-slate-900"
                                ></textarea>
                            </div>
                            <div className="grid grid-cols-2 gap-4">
                                <div>
                                    <label className="mb-1 block text-[10px] font-black tracking-widest text-slate-400 uppercase">
                                        Processing Fee (₱)
                                    </label>
                                    <input
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        value={docForm.data.processing_fee}
                                        onChange={(e) =>
                                            docForm.setData('processing_fee', e.target.value)
                                        }
                                        required
                                        className="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-bold text-slate-800 outline-none focus:ring-2 focus:ring-slate-900"
                                    />
                                </div>
                                <div>
                                    <label className="mb-1 block text-[10px] font-black tracking-widest text-slate-400 uppercase">
                                        Processing Time (Mins)
                                    </label>
                                    <input
                                        type="number"
                                        min="1"
                                        value={docForm.data.processing_time_minutes}
                                        onChange={(e) =>
                                            docForm.setData(
                                                'processing_time_minutes',
                                                e.target.value
                                            )
                                        }
                                        required
                                        className="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-bold text-slate-800 outline-none focus:ring-2 focus:ring-slate-900"
                                    />
                                </div>
                            </div>
                            <div className="mt-6 flex justify-end gap-3 border-t border-slate-100 pt-4">
                                <button
                                    type="button"
                                    onClick={() =>
                                        setDocModal({ isOpen: false, mode: 'add', docId: null })
                                    }
                                    className="rounded-xl bg-slate-200 px-5 py-2.5 text-xs font-black tracking-widest text-slate-700 uppercase transition-all hover:bg-slate-300 active:scale-95"
                                >
                                    Cancel
                                </button>
                                <button
                                    type="submit"
                                    disabled={docForm.processing}
                                    className="rounded-xl bg-slate-900 px-6 py-2.5 text-xs font-black tracking-widest text-white uppercase shadow-md transition-all hover:bg-slate-800 active:scale-95 disabled:opacity-50"
                                >
                                    {docForm.processing ? 'Saving...' : 'Save Document'}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}

            {/* Status Modal */}
            {statusModal.isOpen && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/80 p-4 backdrop-blur-sm">
                    <div className="w-full max-w-sm transform overflow-hidden rounded-2xl border border-slate-100 bg-white p-6 shadow-2xl transition-all">
                        <div className="mb-6 flex flex-col items-center text-center">
                            <div className="mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-slate-100 text-slate-900">
                                <svg
                                    className="h-8 w-8"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        strokeLinecap="round"
                                        strokeLinejoin="round"
                                        strokeWidth="2"
                                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
                                    ></path>
                                </svg>
                            </div>
                            <h3 className="text-xl font-black tracking-tight text-slate-900 uppercase">
                                {statusModal.nextStatus === 'rejected' ? (
                                    <span className="text-red-600">Reject Request?</span>
                                ) : (
                                    <>
                                        Move to{' '}
                                        <span className="text-blue-600">{statusModal.label}</span>?
                                    </>
                                )}
                            </h3>
                            <p className="mt-2 px-4 text-sm font-medium text-slate-500">
                                Sigurado ka bang gusto mong i-update ang status ng request na ito?
                            </p>
                        </div>
                        <form onSubmit={submitStatus}>
                            <div className="flex flex-col gap-3">
                                <button
                                    type="button"
                                    onClick={() =>
                                        setStatusModal({
                                            isOpen: false,
                                            requestId: null,
                                            nextStatus: '',
                                            label: '',
                                        })
                                    }
                                    className="w-full rounded-xl border border-slate-200 bg-white py-3 text-[10px] font-black tracking-widest text-slate-500 uppercase shadow-sm transition-all hover:bg-slate-50 hover:text-slate-900 active:scale-95"
                                >
                                    Cancel
                                </button>
                                <button
                                    type="submit"
                                    disabled={statusForm.processing}
                                    className={`w-full rounded-xl py-3 text-xs font-black tracking-widest text-white uppercase shadow-md transition-all active:scale-95 disabled:opacity-50 ${statusModal.nextStatus === 'rejected' ? 'bg-red-600 hover:bg-red-700' : 'bg-slate-900 hover:bg-slate-800'}`}
                                >
                                    {statusForm.processing
                                        ? 'Updating...'
                                        : statusModal.nextStatus === 'rejected'
                                          ? 'Reject Request'
                                          : 'Confirm Update'}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}

            {/* PDF Viewers */}
            {pdfModalOpen && (
                <div className="fixed inset-0 z-[200] flex items-center justify-center bg-slate-900/90 p-4 backdrop-blur-sm transition-opacity sm:p-8">
                    <div className="flex h-[90vh] w-full max-w-5xl flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl">
                        <div className="flex items-center justify-between border-b border-slate-200 bg-slate-50 p-4">
                            <h2 className="flex items-center gap-2 text-lg font-black tracking-tight text-slate-900 uppercase">
                                <svg
                                    className="h-5 w-5 text-red-600"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        strokeLinecap="round"
                                        strokeLinejoin="round"
                                        strokeWidth="2"
                                        d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                                    ></path>
                                </svg>
                                System Analytics Report
                            </h2>
                            <button
                                onClick={closePdfModal}
                                className="text-3xl leading-none font-bold text-slate-400 transition-all hover:text-red-600"
                            >
                                &times;
                            </button>
                        </div>
                        <div className="relative w-full flex-1 bg-slate-200">
                            {/* SPA iframe trick to load Laravel PDF directly */}
                            <iframe
                                name="pdfViewerFrame"
                                id="pdfViewerFrame"
                                className="relative z-10 h-full w-full bg-white"
                            ></iframe>
                        </div>
                        <div className="flex items-center justify-between border-t border-slate-200 bg-slate-50 p-4">
                            {/* Form para i-force download ang current generated PDF parameters */}
                            <form
                                method="POST"
                                action={route('admin.reports.generate')}
                                target="_blank"
                            >
                                <input
                                    type="hidden"
                                    name="_token"
                                    value={usePage().props.csrf_token}
                                />
                                <input type="hidden" name="report_month" value={reportMonth} />
                                <input type="hidden" name="report_year" value={reportYear} />
                                <input type="hidden" name="is_download" value="1" />
                                <button
                                    type="submit"
                                    className="flex items-center gap-2 rounded-xl bg-red-600 px-6 py-3 text-[10px] font-black tracking-widest text-white uppercase shadow-sm transition-all hover:bg-red-700 active:scale-95"
                                >
                                    <svg
                                        className="h-4 w-4"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            strokeLinecap="round"
                                            strokeLinejoin="round"
                                            strokeWidth="2"
                                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"
                                        ></path>
                                    </svg>
                                    I-Download ang PDF
                                </button>
                            </form>
                            <button
                                onClick={closePdfModal}
                                className="rounded-xl bg-slate-900 px-8 py-3 text-[10px] font-black tracking-widest text-white uppercase shadow-sm transition-all hover:bg-slate-800 active:scale-95"
                            >
                                Isara
                            </button>
                        </div>
                    </div>
                </div>
            )}

            {logbookModalOpen && (
                <div className="fixed inset-0 z-[200] flex items-center justify-center bg-slate-900/90 p-4 backdrop-blur-sm transition-opacity sm:p-8">
                    <div className="flex h-[90vh] w-full max-w-5xl flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl">
                        <div className="flex items-center justify-between border-b border-slate-200 bg-slate-50 p-4">
                            <h2 className="flex items-center gap-2 text-lg font-black tracking-tight text-slate-900 uppercase">
                                <svg
                                    className="h-5 w-5 text-red-600"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        strokeLinecap="round"
                                        strokeLinejoin="round"
                                        strokeWidth="2"
                                        d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                                    ></path>
                                </svg>
                                Official Release Logbook
                            </h2>
                            <button
                                onClick={closeLogbookModal}
                                className="text-3xl leading-none font-bold text-slate-400 transition-all hover:text-red-600"
                            >
                                &times;
                            </button>
                        </div>
                        <div className="relative w-full flex-1 bg-slate-200">
                            <iframe
                                name="logbookViewerFrame"
                                id="logbookViewerFrame"
                                className="relative z-10 h-full w-full bg-white"
                            ></iframe>
                        </div>
                        <div className="flex items-center justify-between border-t border-slate-200 bg-slate-50 p-4">
                            <a
                                href={logbookUrl}
                                download
                                className="flex items-center gap-2 rounded-xl bg-red-600 px-6 py-3 text-[10px] font-black tracking-widest text-white uppercase shadow-sm transition-all hover:bg-red-700 active:scale-95"
                            >
                                <svg
                                    className="h-4 w-4"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        strokeLinecap="round"
                                        strokeLinejoin="round"
                                        strokeWidth="2"
                                        d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"
                                    ></path>
                                </svg>
                                I-Download ang Logbook
                            </a>
                            <button
                                onClick={closeLogbookModal}
                                className="rounded-xl bg-slate-900 px-8 py-3 text-[10px] font-black tracking-widest text-white uppercase shadow-sm transition-all hover:bg-slate-800 active:scale-95"
                            >
                                Isara
                            </button>
                        </div>
                    </div>
                </div>
            )}
            {/* FLOATING ACTION BUTTON (FAB) PARA SA BATCH PROCESSING - CONTEXT AWARE */}
            {(() => {
                // 1. Kuhanin ang buong data ng mga naka-check
                const selectedItems = activeQueue.data
                    ? activeQueue.data.filter((q) => selectedRequests.includes(q.id))
                    : [];

                // 2. Dokumento na STRIKTONG may interview base sa Manual
                const interviewDocsList = [3, 4, 5, 6, 8, 9, 10, 11];

                // 3. I-filter ang mga ELIGIBLE IDs bawat aksyon
                const eligibleForProcess = selectedItems
                    .filter((q) => q.status.toLowerCase() === 'pending')
                    .map((q) => q.id);
                const eligibleForInterview = selectedItems
                    .filter(
                        (q) =>
                            q.status.toLowerCase() === 'processing' &&
                            interviewDocsList.includes(q.document_type_id)
                    )
                    .map((q) => q.id);

                // Pwedeng i-release ang 'processing' (bypass) OR 'for_interview' (tapos na)
                const eligibleForRelease = selectedItems
                    .filter((q) => ['processing', 'for_interview'].includes(q.status.toLowerCase()))
                    .map((q) => q.id);

                const eligibleForReceive = selectedItems
                    .filter((q) => q.status.toLowerCase() === 'released')
                    .map((q) => q.id);
                const eligibleForReject = selectedItems
                    .filter((q) => ['pending', 'processing'].includes(q.status.toLowerCase()))
                    .map((q) => q.id);

                return (
                    <div
                        className={`fixed bottom-8 left-1/2 z-[9] flex items-center gap-4 rounded-full border border-slate-700 bg-slate-900 px-6 py-4 shadow-2xl transition-transform duration-300 ease-in-out sm:bottom-12 ${selectedRequests.length > 0 && activeTab === 'queue' && queueSubTab === 'queue-active' ? '-translate-x-1/2 translate-y-0' : '-translate-x-1/2 translate-y-40'}`}
                    >
                        <span className="text-xs font-black tracking-widest whitespace-nowrap text-white uppercase sm:text-sm">
                            {selectedRequests.length} Selected
                        </span>
                        <div className="h-6 w-px bg-slate-600"></div>
                        <div className="flex flex-wrap gap-2 sm:gap-3">
                            {eligibleForProcess.length > 0 && (
                                <button
                                    onClick={() =>
                                        submitBatchAction('processing', eligibleForProcess)
                                    }
                                    className="rounded-full bg-slate-100 px-4 py-2.5 text-[9px] font-black tracking-widest text-slate-900 uppercase shadow-sm transition-all hover:bg-white active:scale-95 sm:px-6 sm:text-[10px]"
                                >
                                    Process ({eligibleForProcess.length})
                                </button>
                            )}

                            {eligibleForInterview.length > 0 && (
                                <button
                                    onClick={() =>
                                        submitBatchAction('for_interview', eligibleForInterview)
                                    }
                                    className="rounded-full bg-purple-600 px-4 py-2.5 text-[9px] font-black tracking-widest text-white uppercase shadow-sm transition-all hover:bg-purple-500 active:scale-95 sm:px-6 sm:text-[10px]"
                                >
                                    Interview ({eligibleForInterview.length})
                                </button>
                            )}

                            {eligibleForRelease.length > 0 && (
                                <button
                                    onClick={() =>
                                        submitBatchAction('released', eligibleForRelease)
                                    }
                                    className="rounded-full bg-green-600 px-4 py-2.5 text-[9px] font-black tracking-widest text-white uppercase shadow-sm transition-all hover:bg-green-500 active:scale-95 sm:px-6 sm:text-[10px]"
                                >
                                    Release ({eligibleForRelease.length})
                                </button>
                            )}

                            {eligibleForReceive.length > 0 && (
                                <button
                                    onClick={() =>
                                        submitBatchAction('received', eligibleForReceive)
                                    }
                                    className="rounded-full bg-blue-600 px-4 py-2.5 text-[9px] font-black tracking-widest text-white uppercase shadow-sm transition-all hover:bg-blue-500 active:scale-95 sm:px-6 sm:text-[10px]"
                                >
                                    Receive ({eligibleForReceive.length})
                                </button>
                            )}

                            {eligibleForReject.length > 0 && (
                                <button
                                    onClick={() => submitBatchAction('rejected', eligibleForReject)}
                                    className="rounded-full border border-red-500 bg-transparent px-4 py-2.5 text-[9px] font-black tracking-widest text-red-500 uppercase shadow-sm transition-all hover:bg-red-500 hover:text-white active:scale-95 sm:px-6 sm:text-[10px]"
                                >
                                    Reject ({eligibleForReject.length})
                                </button>
                            )}
                        </div>
                    </div>
                );
            })()}

            {/* FLOATING ACTION BUTTON (FAB) PARA SA RESIDENT MASTERLIST BATCH DELETE */}
            <div
                className={`fixed bottom-8 left-1/2 z-[9] flex items-center gap-4 rounded-full border border-slate-700 bg-slate-900 px-6 py-4 shadow-2xl transition-transform duration-300 ease-in-out sm:bottom-12 ${selectedResidents.length > 0 && activeTab === 'residents' ? '-translate-x-1/2 translate-y-0' : '-translate-x-1/2 translate-y-40'}`}
            >
                <span className="text-xs font-black tracking-widest whitespace-nowrap text-white uppercase sm:text-sm">
                    {selectedResidents.length} Selected
                </span>
                <div className="h-6 w-px bg-slate-600"></div>
                <div className="flex flex-wrap gap-2 sm:gap-3">
                    <button
                        onClick={() => setSelectedResidents([])}
                        className="rounded-full bg-slate-700 px-4 py-2.5 text-[9px] font-black tracking-widest text-white uppercase shadow-sm transition-all hover:bg-slate-600 active:scale-95 sm:px-6 sm:text-[10px]"
                    >
                        Cancel
                    </button>
                    <button
                        onClick={() => openDeleteCensusModal('batch')}
                        className="flex items-center gap-2 rounded-full bg-red-600 px-4 py-2.5 text-[9px] font-black tracking-widest text-white uppercase shadow-sm transition-all hover:bg-red-500 active:scale-95 sm:px-6 sm:text-[10px]"
                    >
                        <svg
                            className="h-3 w-3 sm:h-4 sm:w-4"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                strokeLinecap="round"
                                strokeLinejoin="round"
                                strokeWidth="2"
                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                            ></path>
                        </svg>
                        Delete
                    </button>
                </div>
            </div>
            {/* Resident Masterlist Safety Delete Modal */}
            {censusDeleteModalOpen && (
                <div className="fixed inset-0 z-[200] flex items-center justify-center bg-slate-900/80 p-4 backdrop-blur-sm transition-opacity">
                    <div className="w-full max-w-sm transform overflow-hidden rounded-2xl border border-red-100 bg-white p-6 shadow-2xl transition-all">
                        <div className="mb-6 flex flex-col items-center text-center">
                            <div className="mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-red-100 text-red-600">
                                <svg
                                    className="h-8 w-8"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        strokeLinecap="round"
                                        strokeLinejoin="round"
                                        strokeWidth="2"
                                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
                                    ></path>
                                </svg>
                            </div>
                            <h3 className="text-xl font-black tracking-tight text-slate-900 uppercase">
                                Warning: Data Deletion
                            </h3>
                            <p className="mt-2 text-sm font-medium text-slate-500">
                                {censusDeleteTarget === 'batch'
                                    ? `Sigurado ka bang gusto mong burahin ang ${selectedResidents.length} napiling residente?`
                                    : 'Sigurado ka bang gusto mong burahin ang residenteng ito sa masterlist?'}
                            </p>
                            <p className="mt-1 text-xs font-bold text-red-500">
                                Ang aksyong ito ay hindi na mababawi.
                            </p>
                        </div>
                        <form onSubmit={submitCensusDelete}>
                            <div className="mb-6">
                                <label className="mb-2 block text-center text-[10px] font-black tracking-widest text-slate-400 uppercase">
                                    I-type ang "DELETE" para mag-confirm{' '}
                                    <span className="text-red-500">*</span>
                                </label>
                                <input
                                    type="text"
                                    value={censusDeleteConfirm}
                                    onChange={(e) => setCensusDeleteConfirm(e.target.value)}
                                    placeholder="DELETE"
                                    className="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-3 text-center font-black text-slate-900 uppercase placeholder-slate-300 outline-none focus:border-red-500 focus:ring-2 focus:ring-red-500"
                                    required
                                />
                            </div>
                            <div className="flex flex-col gap-3">
                                <button
                                    type="button"
                                    onClick={() => setCensusDeleteModalOpen(false)}
                                    className="w-full rounded-xl border border-slate-200 bg-white py-3 text-[10px] font-black tracking-widest text-slate-500 uppercase transition-all hover:bg-slate-50"
                                >
                                    Cancel
                                </button>
                                <button
                                    type="submit"
                                    disabled={censusDeleteConfirm !== 'DELETE'}
                                    className="w-full rounded-xl bg-red-600 py-3 text-xs font-black tracking-widest text-white uppercase shadow-md transition-all hover:bg-red-700 active:scale-95 disabled:cursor-not-allowed disabled:opacity-50"
                                >
                                    Delete Permanently
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}

            {/* GCASH RECEIPT VIEWER LIGHTBOX */}
            {receiptModalOpen && selectedReceiptPath && (
                <div className="fixed inset-0 z-[11] flex items-center justify-center bg-slate-900/90 p-4 backdrop-blur-sm transition-opacity">
                    <div className="w-full max-w-lg transform overflow-hidden rounded-2xl border border-slate-200 bg-white p-6 shadow-2xl transition-all">
                        <div className="mb-4 flex items-center justify-between border-b border-slate-100 pb-4">
                            <h3 className="text-lg font-black tracking-tight text-slate-900 uppercase">
                                GCash Proof of Payment
                            </h3>
                            <button
                                type="button"
                                onClick={() => {
                                    setReceiptModalOpen(false);
                                    setSelectedReceiptPath('');
                                }}
                                className="text-2xl font-bold text-slate-400 hover:text-red-500"
                            >
                                &times;
                            </button>
                        </div>
                        <div className="flex max-h-[50vh] justify-center overflow-y-auto rounded-xl border border-slate-200 bg-slate-50 p-4">
                            <img
                                src={route('file.serve', { filepath: selectedReceiptPath })}
                                alt="GCash Receipt"
                                className="max-w-full rounded-lg object-contain shadow-md"
                                onError={(e) => {
                                    e.currentTarget.outerHTML =
                                        '<div class="p-8 text-center text-xs font-bold text-red-500 italic">Hindi ma-load ang resibo sa system. Siguraduhing may access ang Admin.</div>';
                                }}
                            />
                        </div>
                        <div className="mt-6 flex justify-end gap-3 border-t border-slate-100 pt-4">
                            <button
                                type="button"
                                onClick={() => {
                                    setReceiptModalOpen(false);
                                    setSelectedReceiptPath('');
                                }}
                                className="w-full rounded-xl bg-slate-900 py-3 text-xs font-black tracking-widest text-white uppercase shadow-md transition-all hover:bg-slate-800"
                            >
                                Isara ang Viewer
                            </button>
                        </div>
                    </div>
                </div>
            )}

            {/* MANUAL KYC OVERRIDE CONFIRMATION MODAL */}
            {manualVerifyConfirmOpen && selectedAccount && (
                <div className="fixed inset-0 z-[11] flex items-center justify-center bg-slate-900/90 p-4 backdrop-blur-sm transition-opacity">
                    <div className="w-full max-w-sm transform overflow-hidden rounded-2xl border border-green-100 bg-white p-6 shadow-2xl transition-all">
                        <div className="mb-6 flex flex-col items-center text-center">
                            <div className="mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-green-100 text-green-600">
                                <svg
                                    className="h-8 w-8"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        strokeLinecap="round"
                                        strokeLinejoin="round"
                                        strokeWidth="2"
                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                                    />
                                </svg>
                            </div>
                            <h3 className="text-xl font-black tracking-tight text-slate-900 uppercase">
                                Manual Verify Resident?
                            </h3>
                            <p className="mt-2 px-4 text-sm font-medium text-slate-500">
                                Sigurado ka bang gusto mong manu-manong i-verify at i-approve si{' '}
                                <strong className="text-slate-900">
                                    {selectedAccount.first_name} {selectedAccount.last_name}
                                </strong>
                                ?
                            </p>
                            <p className="mt-2 text-xs text-slate-400 italic">
                                Ito ay bypass sa automated Google Vision scanner kung nagpakita na
                                sila ng valid ID sa hall.
                            </p>
                        </div>
                        <div className="flex flex-col gap-3">
                            <button
                                type="button"
                                onClick={() => {
                                    setManualVerifyConfirmOpen(false);
                                    setSelectedAccount(null);
                                }}
                                className="w-full rounded-xl border border-slate-200 bg-white py-3 text-[10px] font-black tracking-widest text-slate-500 uppercase transition-all hover:bg-slate-50"
                            >
                                Cancel
                            </button>
                            <button
                                type="button"
                                onClick={submitManualVerify}
                                className="w-full rounded-xl bg-green-600 py-3 text-xs font-black tracking-widest text-white uppercase shadow-md transition-all hover:bg-green-700 active:scale-95"
                            >
                                Approve and Verify
                            </button>
                        </div>
                    </div>
                </div>
            )}
        </AdminLayout>
    );
}
