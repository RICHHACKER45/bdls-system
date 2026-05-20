import React, { useState, useEffect } from 'react';
import { Link, usePage } from '@inertiajs/react';

export default function AdminLayout({ children, activeTab, setActiveTab, pendingCount = 0, queueCount = 0 }) {
    const { auth, flash = {} } = usePage().props;
    const [isSidebarOpen, setIsSidebarOpen] = useState(false);
    const [toast, setToast] = useState({ visible: false, message: '', title: '' });

    const toggleSidebar = () => setIsSidebarOpen(!isSidebarOpen);

    // Robust Toast Logic with Optional Chaining
    useEffect(() => {
        if (flash?.success_message || flash?.success) {
            setToast({
                visible: true,
                message: flash?.success_message || flash?.success,
                title: flash?.success_title || 'Success'
            });
            const timer = setTimeout(() => {
                setToast(prev => ({ ...prev, visible: false }));
            }, 5000);
            return () => clearTimeout(timer);
        }
    }, [flash]);

    const handleTabSwitch = (tabId) => {
        setActiveTab(tabId);
        if (window.innerWidth < 1024) {
            setIsSidebarOpen(false);
        }
    };

    const getTitle = () => {
        switch(activeTab) {
            case 'pending': return 'Pending Registrations';
            case 'queue': return 'Queue & Processing';
            case 'walkin': return 'Walk-in Requests';
            case 'announcements': return 'Announcements';
            case 'audit': return 'System Audit Logs';
            case 'settings': return 'Account Settings';
            default: return 'Admin Portal';
        }
    };

    const currentHour = new Date().getHours();
    const greeting = currentHour < 12 ? 'Good morning' : (currentHour < 18 ? 'Good afternoon' : 'Good evening');
    let title = 'Admin';
    if (auth?.user?.email === 'barangaycap@bdlsgov.ph') title = 'Barangay Captain';
    else if (auth?.user?.email === 'barangaysec@bdlsgov.ph') title = 'Secretary';

    return (
        <div className="flex h-[100dvh] overflow-hidden bg-slate-50 font-sans text-slate-900 antialiased">
            {/* MOBILE OVERLAY */}
            <div 
                className={`fixed inset-0 z-40 bg-slate-900/50 lg:hidden transition-opacity duration-300 ${isSidebarOpen ? 'opacity-100' : 'opacity-0 pointer-events-none'}`} 
                onClick={toggleSidebar}
            ></div>

            {/* SIDEBAR */}
            <aside 
                className={`fixed inset-y-0 left-0 z-50 w-72 transform border-r border-slate-200 bg-white transition-transform duration-300 ease-in-out lg:static lg:flex lg:translate-x-0 lg:flex-col ${isSidebarOpen ? 'translate-x-0' : '-translate-x-full'}`}
            >
                <div className="flex h-16 items-center justify-between border-b border-slate-100 px-6">
                    <div className="flex items-center gap-3">
                        <div className="flex h-8 w-8 items-center justify-center overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
                            <img 
                                src="/images/bdls-logo-large.png" 
                                alt="BDLS Logo" 
                                className="h-full w-full object-cover" 
                                onError={(e) => e.currentTarget.outerHTML = '<span class="text-xs font-bold text-slate-900">BD</span>'} 
                            />
                        </div>
                        <span className="text-lg font-bold tracking-tight">Admin Portal</span>
                    </div>
                    <div className="flex items-center gap-2" title="System is live and syncing">
                        <span className="relative flex h-3 w-3">
                            <span className="absolute inline-flex h-full w-full animate-ping rounded-full bg-green-400 opacity-75"></span>
                            <span className="relative inline-flex h-3 w-3 rounded-full bg-green-500"></span>
                        </span>
                        <span className="text-[10px] font-bold tracking-wider text-slate-400 uppercase">Live</span>
                    </div>
                </div>

                {/* NAVIGATION TABS */}
                <nav className="flex-1 space-y-1 overflow-y-auto px-3 py-4">
                    <button onClick={() => handleTabSwitch('pending')} className={`w-full flex items-center justify-between px-3 py-2.5 rounded-lg font-medium transition-all ${activeTab === 'pending' ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-100'}`}>
                        <div className="flex items-center gap-3">
                            <svg className="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Pending Registrations
                        </div>
                        {pendingCount > 0 && <span className="rounded-full bg-red-500 px-2 py-0.5 text-xs font-bold text-white">{pendingCount}</span>}
                    </button>
                    
                    <button onClick={() => handleTabSwitch('queue')} className={`w-full flex items-center justify-between px-3 py-2.5 rounded-lg font-medium transition-all ${activeTab === 'queue' ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-100'}`}>
                        <div className="flex items-center gap-3">
                            <svg className="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path></svg>
                            Queue & Processing
                        </div>
                        {queueCount > 0 && <span className="rounded-full bg-red-500 px-2 py-0.5 text-xs font-bold text-white">{queueCount}</span>}
                    </button>

                    <button onClick={() => handleTabSwitch('walkin')} className={`w-full flex items-center gap-3 px-3 py-2.5 rounded-lg font-medium transition-all ${activeTab === 'walkin' ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-100'}`}>
                        <svg className="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        Walk-in Requests
                    </button>

                    <button onClick={() => handleTabSwitch('announcements')} className={`w-full flex items-center gap-3 px-3 py-2.5 rounded-lg font-medium transition-all ${activeTab === 'announcements' ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-100'}`}>
                        <svg className="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"></path></svg>
                        Announcements
                    </button>

                    <button onClick={() => handleTabSwitch('audit')} className={`w-full flex items-center gap-3 px-3 py-2.5 rounded-lg font-medium transition-all ${activeTab === 'audit' ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-100'}`}>
                        <svg className="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        System Audit Logs
                    </button>
                </nav>

                <div className="border-t border-slate-100 p-4">
                    <Link 
                        href={route('logout')} 
                        method="post" 
                        as="button" 
                        className="w-full flex items-center justify-center gap-2 rounded-lg border border-red-200 bg-red-50 px-4 py-2 font-bold text-red-700 transition-all hover:bg-red-100 active:scale-95 mb-2"
                    >
                        <svg className="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                        Logout Admin
                    </Link>
                    <button onClick={() => handleTabSwitch('settings')} className={`w-full flex items-center gap-3 px-3 py-2.5 rounded-lg font-medium transition-all ${activeTab === 'settings' ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-100'}`}>
                        <svg className="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        Account Settings
                    </button>
                </div>
            </aside>

            {/* MAIN CONTENT */}
            <div className="flex min-w-0 flex-1 flex-col overflow-hidden">
                <header className="flex h-16 items-center justify-between border-b border-slate-100 bg-white px-4 sm:px-6 lg:px-8">
                    <div className="flex items-center gap-3">
                        <button onClick={toggleSidebar} className="-ml-2 rounded-md p-2 text-slate-500 transition-all hover:text-slate-900 focus:outline-none active:scale-95 lg:hidden">
                            <svg className="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                        </button>
                        <h1 className="hidden text-xl font-bold text-slate-800 sm:block">{getTitle()}</h1>
                    </div>

                    <div className="flex items-center gap-3">
                        <div className="hidden text-right sm:block">
                            <p className="mb-0.5 text-[10px] font-black tracking-widest text-slate-400 uppercase">{greeting},</p>
                            <p className="text-sm leading-none font-bold text-slate-800">{title}!</p>
                        </div>
                        <div className="flex h-9 w-9 items-center justify-center rounded-full border-2 border-white bg-slate-900 text-white shadow-sm">
                            <svg className="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        </div>
                    </div>
                </header>

                <main className="relative flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8">
                    {children}
                </main>
            </div>

            {/* TOAST ALERTS */}
            <div className="pointer-events-none fixed top-6 left-1/2 z-[1000] flex w-full max-w-md -translate-x-1/2 transform flex-col gap-3 px-4">
                <div className={`pointer-events-auto flex transform items-center gap-4 rounded-xl border-l-4 border-green-400 bg-slate-900 px-6 py-4 text-white shadow-2xl transition-all duration-500 ${toast.visible ? 'translate-y-0 opacity-100' : '-translate-y-20 opacity-0'}`}>
                    <svg className="h-6 w-6 shrink-0 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <div>
                        {toast.title && <p className="text-sm font-bold">{toast.title}</p>}
                        <p className="text-sm font-medium">{toast.message}</p>
                    </div>
                </div>
            </div>
        </div>
    );
}
