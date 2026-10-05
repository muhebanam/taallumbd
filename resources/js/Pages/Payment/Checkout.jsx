import { useState } from 'react';
import { Head, Link, router, usePage } from '@inertiajs/react';
import MainLayout from '../../Layouts/MainLayout';

export default function Checkout({ course, pendingOrder, availableGateways = [] }) {
    const { auth, errors: pageErrors, flash } = usePage().props;
    const user = auth?.user;

    const basePrice = Number(course.price) || 0;
    const isFree = course.is_free || basePrice === 0;

    const [couponCode, setCouponCode] = useState(flash?.coupon_code || '');
    const [discount, setDiscount] = useState(flash?.discount_amount || 0);
    const [couponApplied, setCouponApplied] = useState(Boolean(flash?.coupon_success));
    const [couponError, setCouponError] = useState('');
    const [applying, setApplying] = useState(false);
    const [submitting, setSubmitting] = useState(false);
    const [agreePledge, setAgreePledge] = useState(true);

    // Default to first enabled gateway or 'manual'
    const [selectedGateway, setSelectedGateway] = useState(() => {
        if (availableGateways && availableGateways.length > 0) {
            return availableGateways[0].id;
        }
        return 'manual';
    });

    const finalAmount = Math.max(0, basePrice - discount);
    const errorMessage = flash?.error || pageErrors?.error;

    const applyCoupon = (e) => {
        e.preventDefault();
        if (!couponCode.trim()) return;
        setApplying(true);
        setCouponError('');

        router.post(`/checkout/${course.slug}/coupon`, {
            code: couponCode,
        }, {
            preserveScroll: true,
            onSuccess: (page) => {
                setApplying(false);
                if (page.props.flash?.discount_amount) {
                    setDiscount(page.props.flash.discount_amount);
                    setCouponApplied(true);
                }
            },
            onError: (err) => {
                setApplying(false);
                setCouponError(err.coupon || 'কুপনটি গ্রহণ করা হয়নি।');
            }
        });
    };

    const proceedToPay = () => {
        if (!agreePledge) {
            alert('অনুগ্রহ করে ইলম অর্জনের অঙ্গীকারনামা নিশ্চিত করুন।');
            return;
        }

        setSubmitting(true);
        router.post(`/checkout/${course.slug}`, {
            coupon_code: couponApplied ? couponCode : undefined,
            gateway: selectedGateway,
        }, {
            onFinish: () => setSubmitting(false),
        });
    };

    return (
        <MainLayout>
            <Head title={`চেকআউট — ${course.title} | আত-তাআল্লুম`} />

            {/* Header Strip */}
            <div className="bg-gradient-to-b from-[#102526] to-[#1A2E2F] py-10 text-white">
                <div className="mx-auto max-w-4xl px-4 text-center sm:px-6">
                    <span className="rounded-full bg-[#FFF99A]/20 px-3.5 py-1 text-xs font-semibold text-[#FFF99A]">
                        নিরাপদ এনরোলমেন্ট
                    </span>
                    <h1 className="mt-2 text-2xl sm:text-3xl font-extrabold text-white">
                        কোর্স এনরোলমেন্ট ও পেমেন্ট চেকআউট
                    </h1>
                    <p className="mt-1 text-xs text-[#F8FAF8]/80">
                        নিচের ধাপগুলো সম্পন্ন করে অবিলম্বে আপনার প্রিয় কোর্সের ক্লাসরুমে প্রবেশ করুন
                    </p>
                </div>
            </div>

            <div className="mx-auto max-w-4xl px-4 py-10 sm:px-6 lg:px-8">
                {/* Flash Error Banner (e.g. from failed callback or cancelled payment) */}
                {errorMessage && (
                    <div className="mb-6 rounded-2xl bg-rose-50 border border-rose-200 p-4 text-xs font-semibold text-rose-800 flex items-center justify-between">
                        <div className="flex items-center gap-2">
                            <span className="text-base">⚠️</span>
                            <span>{errorMessage}</span>
                        </div>
                    </div>
                )}

                <div className="grid gap-8 md:grid-cols-5">
                    {/* Course Summary Card (3 cols) */}
                    <div className="md:col-span-3 space-y-6">
                        <div className="rounded-3xl border border-gray-200 bg-white p-6 sm:p-8 shadow-sm">
                            <h2 className="text-lg font-bold text-[#102526] border-b border-gray-100 pb-3">
                                নির্বাচিত কোর্সের বিবরণ
                            </h2>

                            <div className="mt-5 flex gap-4">
                                <div className="h-20 w-28 shrink-0 overflow-hidden rounded-2xl bg-[#102526] flex items-center justify-center text-white">
                                    {course.thumbnail ? (
                                        <img src={course.thumbnail} alt={course.title} className="h-full w-full object-cover" />
                                    ) : (
                                        <span className="font-amiri text-xs text-[#FFF99A]">আত-তাআল্লুম</span>
                                    )}
                                </div>
                                <div className="min-w-0 flex-1">
                                    <h3 className="font-bold text-base text-[#102526] leading-snug line-clamp-2">
                                        {course.title}
                                    </h3>
                                    {course.instructor?.name && (
                                        <p className="mt-1 text-xs text-gray-500">
                                            উস্তায: <span className="font-semibold text-gray-700">{course.instructor.name}</span>
                                        </p>
                                    )}
                                    <div className="mt-2 flex flex-wrap items-center gap-2">
                                        <span className="rounded-full bg-emerald-50 px-2.5 py-0.5 text-[10px] font-bold text-emerald-800">
                                            ✓ আজীবন অ্যাক্সেস
                                        </span>
                                        <span className="rounded-full bg-amber-50 px-2.5 py-0.5 text-[10px] font-bold text-amber-800">
                                            ✓ সমাপনী সনদপত্র
                                        </span>
                                    </div>
                                </div>
                            </div>

                            {/* Student Details */}
                            <div className="mt-6 rounded-2xl bg-gray-50 p-4 border border-gray-100 text-xs">
                                <span className="font-bold text-gray-700 block mb-1">শিক্ষার্থীর তথ্য:</span>
                                <div className="grid grid-cols-2 gap-2 text-gray-600">
                                    <div>নাম: <span className="font-semibold text-gray-900">{user?.name}</span></div>
                                    <div>ইমেইল: <span className="font-semibold text-gray-900">{user?.email}</span></div>
                                </div>
                            </div>

                            {/* Halal Pledge */}
                            <div className="mt-6 rounded-2xl border border-emerald-900/10 bg-emerald-50/50 p-4">
                                <label className="flex items-start gap-3 cursor-pointer">
                                    <input
                                        type="checkbox"
                                        checked={agreePledge}
                                        onChange={(e) => setAgreePledge(e.target.checked)}
                                        className="mt-0.5 h-4 w-4 rounded border-gray-300 text-[#102526] focus:ring-[#102526]"
                                    />
                                    <span className="text-xs text-emerald-950 leading-relaxed">
                                        <strong>ইলমী অঙ্গীকারনামা:</strong> আমি একমাত্র মহান আল্লাহর সন্তুষ্টি ও ইলমে দ্বীন অর্জনের উদ্দেশ্যে এই কোর্সে যুক্ত হচ্ছি এবং প্ল্যাটফর্মের নিয়ম ও উস্তাযগণের সম্মান বজায় রাখতে প্রতিশ্রুত।
                                    </span>
                                </label>
                            </div>
                        </div>

                        {/* Payment Gateway Selector */}
                        {!isFree && (
                            <div className="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm space-y-4">
                                <h3 className="font-bold text-sm text-[#102526]">
                                    পেমেন্ট গেটওয়ে নির্বাচন করুন
                                </h3>

                                <div className="space-y-3">
                                    {availableGateways && availableGateways.length > 0 ? (
                                        availableGateways.map((gw) => {
                                            const isSelected = selectedGateway === gw.id;
                                            return (
                                                <label
                                                    key={gw.id}
                                                    onClick={() => setSelectedGateway(gw.id)}
                                                    className={`flex items-center justify-between p-4 rounded-2xl border-2 cursor-pointer transition ${
                                                        isSelected
                                                            ? 'border-[#102526] bg-[#102526]/5 shadow-sm'
                                                            : 'border-gray-100 hover:border-gray-200 bg-white'
                                                    }`}
                                                >
                                                    <div className="flex items-center gap-3">
                                                        <input
                                                            type="radio"
                                                            name="payment_gateway"
                                                            value={gw.id}
                                                            checked={isSelected}
                                                            onChange={() => setSelectedGateway(gw.id)}
                                                            className="h-4 w-4 text-[#102526] focus:ring-[#102526]"
                                                        />
                                                        <div>
                                                            <div className="font-bold text-xs sm:text-sm text-gray-900">
                                                                {gw.name}
                                                            </div>
                                                            <div className="text-[11px] text-gray-500">
                                                                {gw.type === 'automated' ? 'সরাসরি গেটওয়ের মাধ্যমে তাৎক্ষণিক লেনদেন' : 'বিকাশ, নগদ ও রকেটের মাধ্যমে ম্যানুয়াল ভেরিফিকেশন'}
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <span className={`text-[10px] font-bold px-2 py-0.5 rounded-full ${
                                                        gw.type === 'automated'
                                                            ? 'bg-emerald-100 text-emerald-800'
                                                            : 'bg-amber-100 text-amber-800'
                                                    }`}>
                                                        {gw.badge}
                                                    </span>
                                                </label>
                                            );
                                        })
                                    ) : (
                                        <div className="p-3 bg-gray-50 rounded-xl text-xs text-gray-600">
                                            ম্যানুয়াল পেমেন্ট (bKash / Nagad / Rocket) সক্রিয় রয়েছে।
                                        </div>
                                    )}
                                </div>
                            </div>
                        )}
                    </div>

                    {/* Order Summary & Pay Card (2 cols) */}
                    <div className="md:col-span-2 space-y-6">
                        <div className="rounded-3xl border-2 border-[#102526] bg-white p-6 shadow-md">
                            <h3 className="font-bold text-base text-[#102526] border-b border-gray-100 pb-3">
                                পেমেন্ট সংক্ষেপ
                            </h3>

                            <div className="mt-4 space-y-3 text-xs">
                                <div className="flex justify-between text-gray-600">
                                    <span>কোর্স ফি:</span>
                                    <span className="font-bold text-gray-900">
                                        {isFree ? 'ফ্রি' : `৳${basePrice.toFixed(0)}`}
                                    </span>
                                </div>

                                {discount > 0 && (
                                    <div className="flex justify-between text-emerald-700 font-semibold">
                                        <span>কুপন ডিসকাউন্ট:</span>
                                        <span>- ৳{discount.toFixed(0)}</span>
                                    </div>
                                )}

                                <div className="border-t border-gray-200 pt-3 flex justify-between items-baseline text-sm">
                                    <span className="font-bold text-[#102526]">সর্বমোট প্রদেয়:</span>
                                    <span className="text-2xl font-extrabold text-[#102526]">
                                        {isFree || finalAmount <= 0 ? '৳০ (ফ্রি)' : `৳${finalAmount.toFixed(0)}`}
                                    </span>
                                </div>
                            </div>

                            {/* Coupon Code Section */}
                            {!isFree && (
                                <div className="mt-6 border-t border-gray-100 pt-4">
                                    <label className="block text-xs font-bold text-gray-700 mb-1.5">
                                        প্রোমো কোড / কুপন আছে?
                                    </label>
                                    <form onSubmit={applyCoupon} className="flex gap-2">
                                        <input
                                            type="text"
                                            value={couponCode}
                                            onChange={(e) => setCouponCode(e.target.value.toUpperCase())}
                                            placeholder="কুপন লিখুন"
                                            disabled={couponApplied}
                                            className="w-full rounded-xl border border-gray-300 px-3 py-2 text-xs uppercase focus:border-[#102526] focus:outline-none"
                                        />
                                        <button
                                            type="submit"
                                            disabled={applying || couponApplied}
                                            className="rounded-xl bg-[#102526] px-3.5 py-2 text-xs font-bold text-[#FFF99A] hover:bg-[#1A2E2F] disabled:opacity-50"
                                        >
                                            {couponApplied ? 'প্রযোজ্য' : applying ? '...' : 'যোগ করুন'}
                                        </button>
                                    </form>

                                    {couponError && (
                                        <p className="mt-1.5 text-xs text-rose-600">{couponError}</p>
                                    )}
                                    {couponApplied && (
                                        <p className="mt-1.5 text-xs text-emerald-700 font-semibold">
                                            ✓ কুপন সফলভাবে যুক্ত হয়েছে!
                                        </p>
                                    )}
                                </div>
                            )}

                            {/* CTA Proceed Button */}
                            <button
                                onClick={proceedToPay}
                                disabled={submitting}
                                className="mt-6 w-full rounded-2xl bg-[#102526] py-3.5 text-center text-sm font-bold text-[#FFF99A] shadow-lg transition hover:bg-[#1A2E2F] hover:shadow-xl disabled:opacity-60"
                            >
                                {submitting
                                    ? 'পেমেন্ট গেটওয়েতে সংযোগ হচ্ছে...'
                                    : isFree || finalAmount <= 0
                                    ? 'ফ্রি ভর্তি সম্পন্ন করুন'
                                    : `পেমেন্টে এগিয়ে যান — ৳${finalAmount.toFixed(0)}`}
                            </button>

                            <p className="mt-3 text-center text-[11px] text-gray-400">
                                🔒 SSL এনক্রিপ্টেড ১০০% নিরাপদ পেমেন্ট
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </MainLayout>
    );
}
