import React from 'react';
import NavBar from '../Components/NavBar';
import Footer from '../Components/Footer';
import FlashMessage from '../Components/FlashMessage';

export default function MainLayout({ children }) {
    return (
        <div className="flex min-h-screen flex-col bg-[#F8FAF8] text-[#142425]">
            <NavBar />
            <FlashMessage />
            <main className="flex-1">{children}</main>
            <Footer />
        </div>
    );
}
