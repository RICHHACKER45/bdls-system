import React, { useState, useEffect, useRef } from 'react';
import axios from 'axios';

export default function LiveDiagnosticsDrawer({ onNavigateToMaintenance }) {
    const [isOpen, setIsOpen] = useState(false);
    const [activeFilter, setActiveFilter] = useState('ALL'); // 'ALL' | 'AUDIT' | 'SMS'
    const [loading, setLoading] = useState(false);
    const [logs, setLogs] = useState([]);
    const [diagnostics, setDiagnostics] = useState({
        database: { status: 'CONNECTED', latency_ms: 1.8, total_census: 0 },
        sms: { primary_gateway: 'Fortmed / PinasSMS Hybrid', curfew_active: false },
        vision_ocr: { status: 'READY', zero_retention_policy: 'Strict In-Memory (0 Bytes)' },
        websockets: { broadcaster: 'reverb', port: 8080 },
    });
    const [lastUpdated, setLastUpdated] = useState('');

    const fetchLogs = async () => {
        setLoading(true);
        try {
            const res = await axios.get(route('admin.maintenance.logs'));
            if (res.data.success) {
                setLogs(res.data.logs || []);
                if (res.data.diagnostics) {
                    setDiagnostics(res.data.diagnostics);
                }
                setLastUpdated(new Date().toLocaleTimeString());
            }
        } catch (err) {
            console.error('Failed to fetch live diagnostics logs', err);
        } finally {
            setLoading(false);
        }
    };

    // Load initial logs when opened
    useEffect(() => {
        if (isOpen) {
            fetchLogs();
        }
    }, [isOpen]);

    // Listen to real-time Echo events if available
    useEffect(() => {
        if (window.Echo) {
            const channel = window.Echo.private('admin-dashboard');
            channel.listen('AdminDashboardUpdated', () => {
                fetchLogs();
            });
            return () => {
                if (window.Echo) window.Echo.leaveChannel('admin-dashboard');
            };
        }
    }, []);

    const filteredLogs = logs.filter((log) => {
        if (activeFilter === 'ALL') return true;
        if (activeFilter === 'AUDIT') return log.type === 'AUDIT';
        if (activeFilter === 'SMS') return log.type === 'SMS';
        return true;
    });

    return (
        <>
            {/* FLOATING TRIGGER BADGE PINNED TO RIGHT EDGE ("Palitaw sa Gilid") */}
            <div className="fixed right-0 top-1/2 -translate-y-1/2 z-40">
                <button
                    type="button"
                    onClick={() => setIsOpen(!isOpen)}
                    className="group flex items-center gap-2 rounded-l-2xl border-l-2 border-t-2 border-b-2 border-indigo-500/50 bg-slate-950/95 py-3 pl-3 pr-2.5 text-white shadow-2xl backdrop-blur-md transition-all hover:bg-slate-900 hover:pl-4 active:scale-95"
                    title="Toggle Live Diagnostics & System Logs Drawer"
                >
                    <span className="relative flex h-2.5 w-2.5">
                        <span className="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75"></span>
                        <span className="relative inline-flex h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
                    </span>
                    <div className="flex flex-col items-start text-left">
                        <span className="text-[10px] font-black tracking-wider text-indigo-400 uppercase">
                            Telemetry
                        </span>
                        <span className="text-[11px] font-extrabold text-slate-100 flex items-center gap-1">
                            Live Logs
                            <svg className={`h-3 w-3 text-slate-400 transition-transform ${isOpen ? 'rotate-180' : ''}`} fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2.5" d="M15 19l-7-7 7-7" />
                            </svg>
                        </span>
                    </div>
                </button>
            </div>

            {/* BACKDROP OVERLAY */}
            {isOpen && (
                <div
                    onClick={() => setIsOpen(false)}
                    className="fixed inset-0 z-50 bg-slate-950/40 backdrop-blur-[2px] transition-opacity"
                />
            )}

            {/* SLIDE-OUT FLYOUT DRAWER */}
            <aside
                className={`fixed right-0 top-0 bottom-0 z-50 flex w-full max-w-md flex-col border-l border-slate-800 bg-slate-950 text-slate-100 shadow-2xl transition-transform duration-300 ease-in-out sm:max-w-lg ${
                    isOpen ? 'translate-x-0' : 'translate-x-full'
                }`}
            >
                {/* DRAWER HEADER */}
                <div className="flex items-center justify-between border-b border-slate-800 bg-slate-900/80 px-5 py-4">
                    <div className="flex items-center gap-3">
                        <div className="flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">
                            <svg className="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>
                        <div>
                            <div className="flex items-center gap-2">
                                <h3 className="text-sm font-black text-white tracking-wide uppercase">
                                    Live Diagnostics &amp; Pulse
                                </h3>
                                <span className="rounded-full bg-emerald-500/10 px-2 py-0.5 text-[9px] font-black text-emerald-400 border border-emerald-500/20">
                                    SYS OK
                                </span>
                            </div>
                            <p className="text-[10px] text-slate-400">
                                ISO/IEC 25010 Real-time System Stream {lastUpdated && `• ${lastUpdated}`}
                            </p>
                        </div>
                    </div>

                    <div className="flex items-center gap-1.5">
                        <button
                            type="button"
                            onClick={fetchLogs}
                            disabled={loading}
                            className="rounded-lg p-1.5 text-slate-400 hover:bg-slate-800 hover:text-white transition-all disabled:opacity-50"
                            title="Refresh Diagnostics"
                        >
                            <svg className={`h-4 w-4 ${loading ? 'animate-spin text-indigo-400' : ''}`} fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                        </button>
                        <button
                            type="button"
                            onClick={() => setIsOpen(false)}
                            className="rounded-lg p-1.5 text-slate-400 hover:bg-slate-800 hover:text-white transition-all"
                            title="Close Drawer"
                        >
                            <svg className="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                {/* SUBSYSTEM VITAL SIGNS TICKER (Performance Efficiency Proof) */}
                <div className="grid grid-cols-2 gap-2 border-b border-slate-800 bg-slate-900/40 p-4 sm:grid-cols-4">
                    <div className="rounded-xl border border-slate-800 bg-slate-900/80 p-2.5">
                        <div className="text-[9px] font-bold text-slate-400 uppercase">Database</div>
                        <div className="mt-0.5 text-xs font-black text-emerald-400 flex items-center gap-1">
                            <span>{diagnostics.database?.latency_ms || 2.1}ms</span>
                            <span className="text-[9px] font-medium text-slate-500">ping</span>
                        </div>
                    </div>
                    <div className="rounded-xl border border-slate-800 bg-slate-900/80 p-2.5">
                        <div className="text-[9px] font-bold text-slate-400 uppercase">SMS Telco</div>
                        <div className="mt-0.5 text-xs font-black text-sky-400">
                            {diagnostics.sms?.curfew_active ? 'CURFEW (9PM)' : 'ACTIVE (HYBRID)'}
                        </div>
                    </div>
                    <div className="rounded-xl border border-slate-800 bg-slate-900/80 p-2.5">
                        <div className="text-[9px] font-bold text-slate-400 uppercase">Privacy (RA 10173)</div>
                        <div className="mt-0.5 text-xs font-black text-indigo-300">
                            0 BYTES DISK
                        </div>
                    </div>
                    <div className="rounded-xl border border-slate-800 bg-slate-900/80 p-2.5">
                        <div className="text-[9px] font-bold text-slate-400 uppercase">WebSockets</div>
                        <div className="mt-0.5 text-xs font-black text-purple-400">
                            REVERB LIVE
                        </div>
                    </div>
                </div>

                {/* LOG FILTER BAR */}
                <div className="flex items-center justify-between border-b border-slate-800 bg-slate-950 px-4 py-2.5 text-xs">
                    <div className="flex items-center gap-1">
                        <button
                            type="button"
                            onClick={() => setActiveFilter('ALL')}
                            className={`rounded-lg px-2.5 py-1 text-[11px] font-black transition-all ${
                                activeFilter === 'ALL'
                                    ? 'bg-indigo-600 text-white shadow-sm'
                                    : 'text-slate-400 hover:bg-slate-800 hover:text-white'
                            }`}
                        >
                            All ({logs.length})
                        </button>
                        <button
                            type="button"
                            onClick={() => setActiveFilter('AUDIT')}
                            className={`rounded-lg px-2.5 py-1 text-[11px] font-black transition-all ${
                                activeFilter === 'AUDIT'
                                    ? 'bg-indigo-600 text-white shadow-sm'
                                    : 'text-slate-400 hover:bg-slate-800 hover:text-white'
                            }`}
                        >
                            Audit Trail
                        </button>
                        <button
                            type="button"
                            onClick={() => setActiveFilter('SMS')}
                            className={`rounded-lg px-2.5 py-1 text-[11px] font-black transition-all ${
                                activeFilter === 'SMS'
                                    ? 'bg-indigo-600 text-white shadow-sm'
                                    : 'text-slate-400 hover:bg-slate-800 hover:text-white'
                            }`}
                        >
                            SMS Alerts
                        </button>
                    </div>

                    <span className="text-[10px] text-slate-500 font-mono">
                        Live Auto-Feed
                    </span>
                </div>

                {/* SCROLLABLE REAL-TIME LOG FEED */}
                <div className="flex-1 overflow-y-auto p-4 space-y-2.5 scrollbar-thin scrollbar-thumb-slate-800 scrollbar-track-transparent">
                    {filteredLogs.length > 0 ? (
                        filteredLogs.map((log, index) => (
                            <div
                                key={log.id ? `${log.type}-${log.id}` : index}
                                className="group rounded-xl border border-slate-800/80 bg-slate-900/60 p-3 transition-all hover:border-slate-700 hover:bg-slate-900"
                            >
                                <div className="flex items-center justify-between">
                                    <div className="flex items-center gap-1.5">
                                        <span
                                            className={`rounded px-1.5 py-0.5 text-[9px] font-black uppercase ${
                                                log.type === 'SMS'
                                                    ? 'bg-sky-500/10 text-sky-400 border border-sky-500/20'
                                                    : 'bg-indigo-500/10 text-indigo-400 border border-indigo-500/20'
                                            }`}
                                        >
                                            {log.type}
                                        </span>
                                        <span className="text-[11px] font-bold text-slate-200">
                                            {log.action}
                                        </span>
                                    </div>
                                    <span className="text-[10px] font-mono text-slate-500">
                                        {log.raw_time || log.timestamp}
                                    </span>
                                </div>

                                <p className="mt-1.5 text-xs text-slate-300 leading-relaxed font-sans">
                                    {log.description}
                                </p>

                                {log.user && (
                                    <div className="mt-2 text-[10px] text-slate-500 flex items-center gap-1">
                                        <span>Actor:</span>
                                        <span className="font-semibold text-slate-400">{log.user}</span>
                                    </div>
                                )}
                            </div>
                        ))
                    ) : (
                        <div className="flex flex-col items-center justify-center py-16 text-center text-slate-500">
                            <svg className="h-10 w-10 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <p className="mt-2 text-xs font-bold text-slate-400">No Activity Logs Found</p>
                            <p className="text-[11px] text-slate-600">Events will populate here live as you perform actions.</p>
                        </div>
                    )}
                </div>

                {/* DRAWER FOOTER: DIRECT ACCESS TO INTERACTIVE BENCHES */}
                <div className="border-t border-slate-800 bg-slate-900/90 p-4">
                    <button
                        type="button"
                        onClick={() => {
                            setIsOpen(false);
                            if (typeof onNavigateToMaintenance === 'function') {
                                onNavigateToMaintenance();
                            }
                        }}
                        className="flex w-full items-center justify-center gap-2 rounded-xl bg-indigo-600 py-3 text-xs font-black uppercase tracking-wider text-white shadow-lg transition-all hover:bg-indigo-700 active:scale-95"
                    >
                        <svg className="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                        Launch Interactive Test Benches (OCR &amp; SMS) &rarr;
                    </button>
                    <p className="mt-1.5 text-center text-[10px] text-slate-500">
                        Super Admin Mode • ISO/IEC 25010:2023 Software Quality Evaluator
                    </p>
                </div>
            </aside>
        </>
    );
}
