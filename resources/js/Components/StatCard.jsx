export default function StatCard({ value, label, icon, className = "" }) {
    if (!icon) {
        return (
            <div className={`card p-6 bg-white border border-brand/5 shadow-card hover:shadow-cardHover text-center transition-all duration-300 ${className}`}>
                <p className="text-3xl font-extrabold text-brand-deep tracking-tight">{value}</p>
                <p className="mt-1.5 text-sm font-semibold text-brand-text/60">{label}</p>
            </div>
        );
    }
    return (
        <div className={`card p-6 bg-white border border-brand/5 shadow-card hover:shadow-cardHover flex items-center gap-4 transition-all duration-300 ${className}`}>
            <div className="rounded-xl bg-brand-light p-3 text-brand shrink-0">
                {icon}
            </div>
            <div className="min-w-0 flex-1 text-left">
                <p className="text-2xl font-extrabold text-brand-deep tracking-tight">{value}</p>
                <p className="mt-0.5 text-xs font-semibold text-brand-text/60 truncate">{label}</p>
            </div>
        </div>
    );
}
