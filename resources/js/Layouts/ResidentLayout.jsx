import React, { useState, useEffect } from 'react';
import { Link, usePage, router } from '@inertiajs/react';

export default function ResidentLayout({ children }) {
    const { auth, flash = {} } = usePage().props;
    const [isSidebarOpen, setIsSidebarOpen] = useState(false);
    const [toast, setToast] = useState({ visible: false, message: '', title: '' });

    const toggleSidebar = () => setIsSidebarOpen(!isSidebarOpen);

    // Toast Logic
    useEffect(() => {
        if (flash.success_message || flash?.success) {
            setToast({
                visible: true,
                message: flash.success_message || flash.success,
                title: flash.success_title || 'Success'
            });
            const timer = setTimeout(() => {
                setToast(prev => ({ ...prev, visible: false }));
            }, 5000);
            return () => clearTimeout(timer);
        }
    }, [flash]);

    return (
        <div className="flex h-[100dvh] overflow-hidden bg-slate-50 font-sans text-slate-900 antialiased">
            {/* MOBILE OVERLAY */}
            <div 
                className={`fixed inset-0 z-40 bg-slate-900/50 lg:hidden transition-opacity duration-300 ${isSidebarOpen ? 'opacity-100' : 'opacity-0 pointer-events-none'}`} 
                onClick={toggleSidebar}
            ></div>

            {/* SIDEBAR */}
            <aside 
                className={`fixed inset-y-0 left-0 z-50 w-64 transform border-r border-slate-200 bg-white transition-transform duration-300 ease-in-out lg:static lg:flex lg:translate-x-0 lg:flex-col ${isSidebarOpen ? 'translate-x-0' : '-translate-x-full'}`}
            >
                <div className="flex h-16 items-center border-b border-slate-100 px-6">
                    <div className="mr-3 flex h-8 w-8 items-center justify-center overflow-hidden rounded-full border border-slate-200 bg-white shadow-sm">
                        <img 
                            src="/images/bdls-logo-large.png" 
                            alt="BDLS Logo" 
                            className="h-full w-full object-cover" 
                            onError={(e) => {
                                e.currentTarget.outerHTML = '<span class="text-xs font-bold text-red-600">BD</span>';
                            }}
                        />
                    </div>
                    <span className="text-lg font-bold tracking-tight">BDLS System</span>
                </div>

                <nav className="flex-1 space-y-1 overflow-y-auto px-3 py-4">
                    {/* Navigation Buttons are handled by page state, so we use a common structure here */}
                    {/* In a real SPA, these might be actual links if they were separate routes, 
                        but based on the old code, they were tab switchers. 
                        We'll pass the setter down or handle it via URL if needed. 
                        For now, let's keep the layout generic and handle tab-specific active states in the Page. */}
                    
                    <Link 
                        href={route('resident.dashboard')}
                        className={`flex w-full items-center gap-3 rounded-lg px-3 py-2.5 font-medium transition-all ${route().current('resident.dashboard') ? 'bg-red-50 text-red-700' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'}`}
                    >
                        <svg className="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                        Dashboard
                    </Link>
                </nav>

                <div className="border-t border-slate-100 p-4">
                    <Link 
                        href={route('logout')} 
                        method="post" 
                        as="button" 
                        className="flex w-full items-center justify-center gap-2 rounded-lg bg-slate-100 px-4 py-2 font-bold text-slate-700 transition-all hover:bg-slate-200 active:scale-95"
                    >
                        <svg className="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                        Logout
                    </Link>
                </div>
            </aside>

            {/* MAIN CONTENT */}
            <div className="flex min-w-0 flex-1 flex-col overflow-hidden">
                <header className="flex h-16 items-center justify-between border-b border-slate-100 bg-white px-4 sm:px-6 lg:px-8">
                    <button onClick={toggleSidebar} className="-ml-2 rounded-md p-2 text-slate-500 hover:text-slate-900 focus:ring-2 focus:ring-slate-200 focus:outline-none lg:hidden">
                        <svg className="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                    </button>
                    <div className="ml-auto flex items-center gap-3">
                        <span className="max-w-[180px] truncate text-sm font-semibold text-slate-700 sm:max-w-xs">Kamusta, {auth.user.first_name}!</span>
                        <div className="h-9 w-9 overflow-hidden rounded-full border-2 border-white bg-slate-200 shadow-sm">
                            <img src={route('secure.file', { filepath: auth.user.selfie_photo_path })} alt="Profile" className="h-full w-full object-cover" />
                        </div>
                    </div>
                </header>

                <main className="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8">
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
