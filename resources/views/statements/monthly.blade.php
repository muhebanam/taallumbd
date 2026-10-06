<!DOCTYPE html>
<html lang="bn" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>মাসিক আর্থিক স্টেটমেন্ট - {{ $teacher->name }} ({{ $period_label }})</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&family=Outfit:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --brand-dark: #102526;
            --brand-deep: #1A2E2F;
            --brand-gold: #D4AF37;
            --brand-cream: #FFF99A;
            --brand-light: #F8FAF8;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Hind Siliguri', 'Outfit', sans-serif;
            background-color: #f1f5f9;
            color: #1e293b;
            padding: 40px 20px;
            font-size: 14px;
            line-height: 1.5;
        }

        .statement-container {
            max-width: 850px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
            padding: 40px 48px;
            border: 1px solid #e2e8f0;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #f1f5f9;
            padding-bottom: 24px;
            margin-bottom: 28px;
        }

        .brand-logo {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .logo-badge {
            width: 44px;
            height: 44px;
            background: var(--brand-dark);
            color: var(--brand-cream);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 20px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .brand-title {
            font-size: 22px;
            font-weight: 700;
            color: var(--brand-dark);
            letter-spacing: -0.5px;
        }

        .brand-sub {
            font-size: 12px;
            color: #64748b;
        }

        .statement-meta {
            text-align: right;
        }

        .statement-badge {
            display: inline-block;
            background: #ecfdf5;
            color: #065f46;
            font-weight: 700;
            font-size: 12px;
            padding: 4px 12px;
            border-radius: 20px;
            border: 1px solid #a7f3d0;
            margin-bottom: 6px;
        }

        .meta-no {
            font-family: 'Outfit', sans-serif;
            font-size: 13px;
            font-weight: 600;
            color: #334155;
        }

        .meta-date {
            font-size: 12px;
            color: #64748b;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
            margin-bottom: 28px;
            background: #f8fafc;
            padding: 20px 24px;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
        }

        .info-block h4 {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #64748b;
            margin-bottom: 6px;
            font-weight: 700;
        }

        .info-block p {
            font-weight: 600;
            color: #0f172a;
            font-size: 14px;
        }

        .info-block span {
            font-size: 12px;
            color: #64748b;
        }

        .kpi-row {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            margin-bottom: 32px;
        }

        .kpi-card {
            border: 1px solid #e2e8f0;
            padding: 16px;
            border-radius: 12px;
            background: #ffffff;
        }

        .kpi-card.highlight {
            background: #f0fdf4;
            border-color: #bbf7d0;
        }

        .kpi-title {
            font-size: 11px;
            color: #64748b;
            font-weight: 600;
            margin-bottom: 4px;
        }

        .kpi-amount {
            font-size: 18px;
            font-weight: 700;
            color: var(--brand-dark);
            font-family: 'Outfit', sans-serif;
        }

        .section-title {
            font-size: 15px;
            font-weight: 700;
            color: var(--brand-dark);
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 32px;
            font-size: 13px;
        }

        th {
            background: #f8fafc;
            color: #475569;
            text-align: left;
            padding: 10px 14px;
            font-weight: 600;
            border-bottom: 2px solid #e2e8f0;
        }

        td {
            padding: 12px 14px;
            border-bottom: 1px solid #f1f5f9;
            color: #334155;
        }

        tr:last-child td {
            border-bottom: none;
        }

        .amount-pos {
            color: #059669;
            font-weight: 700;
            font-family: 'Outfit', sans-serif;
        }

        .amount-neg {
            color: #dc2626;
            font-weight: 700;
            font-family: 'Outfit', sans-serif;
        }

        .badge {
            display: inline-block;
            font-size: 11px;
            font-weight: 700;
            padding: 2px 8px;
            border-radius: 6px;
        }

        .badge-credit {
            background: #ecfdf5;
            color: #065f46;
        }

        .badge-debit {
            background: #fef2f2;
            color: #991b1b;
        }

        .footer {
            border-top: 1px solid #e2e8f0;
            padding-top: 20px;
            text-align: center;
            font-size: 12px;
            color: #94a3b8;
        }

        .actions {
            margin-top: 24px;
            display: flex;
            justify-content: flex-end;
            gap: 12px;
        }

        .btn {
            background: var(--brand-dark);
            color: #ffffff;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 13px;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            text-decoration: none;
        }

        .btn:hover {
            background: #1a3c3e;
        }

        @media print {
            body {
                background: #ffffff;
                padding: 0;
            }
            .statement-container {
                box-shadow: none;
                border: none;
                padding: 20px;
                max-width: 100%;
            }
            .actions {
                display: none;
            }
        }
    </style>
</head>
<body>

<div class="statement-container">
    <!-- Header -->
    <div class="header">
        <div class="brand-logo">
            <div class="logo-badge">ত</div>
            <div>
                <h1 class="brand-title">তা'ল্লুম বিডি (Taallum BD)</h1>
                <p class="brand-sub">ইসলামিক লার্নিং অ্যান্ড স্কলার এডটেক ইকোসিস্টেম</p>
            </div>
        </div>
        <div class="statement-meta">
            <span class="statement-badge">মাসিক আর্থিক স্টেটমেন্ট</span>
            <div class="meta-no">{{ $statement_no }}</div>
            <div class="meta-date">তারিখ: {{ $generated_at }}</div>
        </div>
    </div>

    <!-- Info Grid -->
    <div class="info-grid">
        <div class="info-block">
            <h4>শিক্ষক / প্রাপক বিবরণ</h4>
            <p>{{ $teacher->name }}</p>
            <span>{{ $teacher->designation ?: 'কোর্স শিক্ষক / গবেষক' }}</span><br>
            <span>ইমেইল: {{ $teacher->email ?: $user->email }}</span>
        </div>
        <div class="info-block">
            <h4>স্টেটমেন্ট সময়কাল ও ওয়ালেট</h4>
            <p>{{ $period_label }}</p>
            <span>ওয়ালেট আইডি: #WLT-{{ str_pad($wallet->id, 5, '0', STR_PAD_LEFT) }}</span><br>
            <span>মুদ্রা: {{ $wallet->currency }} (BDT)</span>
        </div>
    </div>

    <!-- KPI Summary Row -->
    <div class="kpi-row">
        <div class="kpi-card highlight">
            <div class="kpi-title">চলতি উত্তোলনযোগ্য ব্যালেন্স</div>
            <div class="kpi-amount">৳ {{ number_format($wallet->balance_bdt, 2) }}</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-title">স্থগিত (Pending Escrow)</div>
            <div class="kpi-amount">৳ {{ number_format($wallet->pending_balance_bdt, 2) }}</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-title">এই মাসের মোট আয়</div>
            <div class="kpi-amount">৳ {{ number_format($month_credits_bdt, 2) }}</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-title">এই মাসের পেআউট উত্তোলন</div>
            <div class="kpi-amount">৳ {{ number_format($month_debits_bdt, 2) }}</div>
        </div>
    </div>

    <!-- Ledger Table -->
    <div class="section-title">
        <span>মাসিক লেনদেন তালিকা (Wallet Ledger)</span>
        <span style="font-size: 12px; font-weight: normal; color: #64748b;">মোট এন্ট্রি: {{ $transactions->count() }} টি</span>
    </div>

    <table>
        <thead>
            <tr>
                <th>তারিখ ও সময়</th>
                <th>বিবরণ</th>
                <th>টাইপ</th>
                <th>রেফারেন্স</th>
                <th style="text-align: right;">পরিমাণ (BDT)</th>
                <th style="text-align: right;">ব্যালেন্স (BDT)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transactions as $t)
                <tr>
                    <td>{{ $t->created_at->format('d M, Y h:i A') }}</td>
                    <td>{{ $t->description }}</td>
                    <td>
                        <span class="badge {{ $t->type === 'credit' ? 'badge-credit' : 'badge-debit' }}">
                            {{ $t->type === 'credit' ? 'জমা (Credit)' : 'কর্তন (Debit)' }}
                        </span>
                    </td>
                    <td><span style="font-family: 'Outfit'; font-size: 11px;">{{ strtoupper($t->reference_type) }} #{{ $t->reference_id }}</span></td>
                    <td style="text-align: right;" class="{{ $t->type === 'credit' ? 'amount-pos' : 'amount-neg' }}">
                        {{ $t->type === 'credit' ? '+' : '-' }} ৳ {{ number_format($t->amount_bdt, 2) }}
                    </td>
                    <td style="text-align: right; font-family: 'Outfit'; font-weight: 600;">
                        ৳ {{ number_format($t->balance_after_bdt, 2) }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align: center; color: #94a3b8; padding: 24px;">
                        এই মাসে কোনো লেনদেন রেকর্ড পাওয়া যায়নি।
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if($payouts->isNotEmpty())
        <!-- Payout Requests Section -->
        <div class="section-title">
            <span>উত্তোলন অনুরোধসমূহ (Payout Requests)</span>
        </div>
        <table>
            <thead>
                <tr>
                    <th>তারিখ</th>
                    <th>মাধ্যম</th>
                    <th>পরিমাণ</th>
                    <th>স্ট্যাটাস</th>
                    <th>রেফারেন্স নম্বর</th>
                </tr>
            </thead>
            <tbody>
                @foreach($payouts as $p)
                    <tr>
                        <td>{{ $p->requested_at?->format('d M, Y') }}</td>
                        <td>{{ strtoupper($p->method) }}</td>
                        <td style="font-family: 'Outfit'; font-weight: 700;">৳ {{ number_format($p->amount_bdt, 2) }}</td>
                        <td>
                            <span class="badge {{ $p->status === 'paid' ? 'badge-credit' : ($p->status === 'pending' ? 'badge-debit' : '') }}">
                                {{ $p->status === 'paid' ? 'পরিশোধিত' : ($p->status === 'pending' ? 'অপেক্ষমাণ' : $p->status) }}
                            </span>
                        </td>
                        <td style="font-family: 'Outfit'; font-size: 11px;">{{ $p->transaction_reference ?: '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <!-- Footer Note -->
    <div class="footer">
        <p>এটি একটি ডিজিটাল সিস্টেমে তৈরি আর্থিক স্টেটমেন্ট, এতে কোনো স্বাক্ষরের প্রয়োজন নেই।</p>
        <p style="margin-top: 4px;">প্রশ্ন বা সংশোধনের জন্য ইমেইল করুন: finance@taallumbd.com | Taallum BD Platform</p>
    </div>

    <!-- Print Action Buttons -->
    <div class="actions">
        <button onclick="window.print()" class="btn">
            <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24"><path d="M19 8H5c-1.66 0-3 1.34-3 3v6h4v4h12v-4h4v-6c0-1.66-1.34-3-3-3zm-3 11H8v-5h8v5zm3-7c-.55 0-1-.45-1-1s.45-1 1-1 1 .45 1 1-.45 1-1 1zm-1-9H6v4h12V3z"/></svg>
            প্রিন্ট / PDF সংরক্ষণ করুন
        </button>
    </div>
</div>

</body>
</html>
