@extends('layouts.app')
@section('title', 'Revenue Report')
@section('page-title', 'Revenue Report')

@section('topbar-actions')
    {{-- Year selector --}}
    <form method="GET" action="{{ route('revenue.index') }}" style="display:flex;align-items:center;gap:8px">
        <select name="year" class="form-control" style="width:110px;padding:6px 10px;font-size:13px"
                onchange="this.form.submit()" aria-label="Select year">
            @foreach($availableYears as $yr)
                <option value="{{ $yr }}" {{ $yr === $selectedYear ? 'selected' : '' }}>{{ $yr }}</option>
            @endforeach
        </select>
    </form>
    <a href="{{ route('payments.create') }}" class="btn btn-primary btn-sm">
        <i class="fas fa-plus" aria-hidden="true"></i> <span>New Payment</span>
    </a>
@endsection

@section('styles')
<style>
/* ── Summary cards ─────────────────────────────────── */
.rev-summary {
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(200px,1fr));
    gap:14px; margin-bottom:20px;
}
.rev-card {
    background:var(--bg-card); border:1px solid var(--border);
    border-radius:var(--radius); padding:20px;
    display:flex; align-items:flex-start; gap:14px;
    box-shadow:var(--shadow-card);
}
.rev-card-icon {
    width:46px; height:46px; border-radius:10px;
    display:flex; align-items:center; justify-content:center;
    font-size:20px; flex-shrink:0;
}
.rev-card-val   { font-size:22px; font-weight:900; color:var(--text-heading); line-height:1; }
.rev-card-label { font-size:12px; color:var(--text-muted); font-weight:500; margin-top:4px; }
.rev-card-sub   { font-size:11px; color:var(--text-muted); margin-top:2px; }

/* ── Chart container ───────────────────────────────── */
.chart-wrap {
    position:relative; width:100%;
}
.bar-chart {
    display:flex; align-items:flex-end;
    gap:6px; height:200px; padding:0 4px;
}
.bar-col {
    flex:1; display:flex; flex-direction:column;
    align-items:center; gap:4px; height:100%;
    cursor:pointer; position:relative;
}
.bar-col:hover .bar-fill { filter:brightness(1.15); }
.bar-fill {
    width:100%; border-radius:5px 5px 0 0;
    transition:height 0.5s cubic-bezier(.4,0,.2,1), opacity 0.3s;
    position:relative;
}
.bar-fill.current-month { background:var(--success) !important; }
.bar-fill.future-month  { background:var(--border) !important; opacity:0.5; }
.bar-fill.normal-month  { background:var(--primary); }
.bar-label {
    font-size:10px; color:var(--text-muted);
    white-space:nowrap; text-align:center;
}
.bar-val {
    font-size:10px; font-weight:700; color:var(--text-secondary);
    text-align:center; white-space:nowrap;
}
/* Tooltip */
.bar-tooltip {
    position:absolute; bottom:calc(100% + 6px); left:50%;
    transform:translateX(-50%);
    background:var(--bg-sidebar); color:#fff;
    font-size:11px; font-weight:600;
    padding:5px 9px; border-radius:6px;
    white-space:nowrap; pointer-events:none;
    opacity:0; transition:opacity 0.15s;
    z-index:10;
}
.bar-col:hover .bar-tooltip { opacity:1; }

/* ── Trend chart (24 months) ───────────────────────── */
.trend-chart {
    display:flex; align-items:flex-end;
    gap:3px; height:80px; padding:0 2px;
}
.trend-bar {
    flex:1; border-radius:3px 3px 0 0;
    background:var(--primary); opacity:0.6;
    transition:height 0.4s ease, opacity 0.2s;
    cursor:pointer; position:relative;
}
.trend-bar:hover { opacity:1; }
.trend-bar.current { background:var(--success); opacity:1; }

/* ── Monthly table ─────────────────────────────────── */
.month-row { cursor:pointer; transition:background 0.1s; }
.month-row:hover td { background:var(--bg-hover) !important; }
.month-row.current-row td { background:rgba(14,159,110,0.06) !important; }
.month-row.future-row td  { opacity:0.45; }

/* ── Progress bar ──────────────────────────────────── */
.progress-bar-wrap {
    background:var(--bg-muted); border-radius:20px;
    height:6px; overflow:hidden; flex:1;
}
.progress-bar-fill {
    height:100%; border-radius:20px;
    background:var(--primary);
    transition:width 0.6s ease;
}
.progress-bar-fill.current { background:var(--success); }
.progress-bar-fill.future  { background:var(--border); }

/* ── Grade breakdown ───────────────────────────────── */
.grade-grid {
    display:grid;
    grid-template-columns:repeat(auto-fill,minmax(140px,1fr));
    gap:10px;
}
.grade-chip {
    background:var(--bg-muted); border:1px solid var(--border);
    border-radius:var(--radius-sm); padding:12px 14px;
    text-align:center;
}
.grade-chip-val   { font-size:18px; font-weight:800; color:var(--primary); }
.grade-chip-label { font-size:11px; color:var(--text-muted); margin-top:2px; }
.grade-chip-count { font-size:10px; color:var(--text-muted); }

/* ── Layout ────────────────────────────────────────── */
.rev-grid {
    display:grid;
    grid-template-columns:1fr 340px;
    gap:18px; align-items:start;
}
@media(max-width:900px) { .rev-grid { grid-template-columns:1fr; } }
</style>
@endsection

@section('content')

{{-- ── Top summary cards ──────────────────────────────── --}}
<div class="rev-summary">
    {{-- This month --}}
    <div class="rev-card">
        <div class="rev-card-icon" style="background:var(--success-light)">
            <i class="fas fa-calendar-day" style="color:var(--success)"></i>
        </div>
        <div>
            <div class="rev-card-val">${{ number_format($thisMonthAmount, 2) }}</div>
            <div class="rev-card-label">This Month</div>
            <div class="rev-card-sub">{{ $thisMonthCount }} payment{{ $thisMonthCount !== 1 ? 's' : '' }} · {{ now()->format('F Y') }}</div>
        </div>
    </div>

    {{-- Selected year total --}}
    <div class="rev-card">
        <div class="rev-card-icon" style="background:var(--primary-50)">
            <i class="fas fa-chart-bar" style="color:var(--primary)"></i>
        </div>
        <div>
            <div class="rev-card-val">${{ number_format($yearTotal, 2) }}</div>
            <div class="rev-card-label">{{ $selectedYear }} Total</div>
            <div class="rev-card-sub">{{ $yearPaidCount }} payment{{ $yearPaidCount !== 1 ? 's' : '' }} this year</div>
        </div>
    </div>

    {{-- Expected monthly --}}
    <div class="rev-card">
        <div class="rev-card-icon" style="background:var(--warning-light)">
            <i class="fas fa-bullseye" style="color:var(--warning)"></i>
        </div>
        <div>
            <div class="rev-card-val">${{ number_format($expectedMonthly, 2) }}</div>
            <div class="rev-card-label">Expected / Month</div>
            <div class="rev-card-sub">{{ $activeStudentCount }} active students</div>
        </div>
    </div>

    {{-- All-time total --}}
    <div class="rev-card">
        <div class="rev-card-icon" style="background:rgba(124,58,237,0.1)">
            <i class="fas fa-coins" style="color:#7c3aed"></i>
        </div>
        <div>
            <div class="rev-card-val">${{ number_format($allTimeTotal, 2) }}</div>
            <div class="rev-card-label">All-Time Total</div>
            <div class="rev-card-sub">{{ $allTimeCount }} payments ever</div>
        </div>
    </div>
</div>

<div class="rev-grid">

    {{-- ── LEFT: Main charts + table ──────────────────── --}}
    <div style="display:flex;flex-direction:column;gap:18px">

        {{-- Monthly bar chart for selected year --}}
        <div class="card">
            <div class="card-header">
                <div class="card-title">
                    <i class="fas fa-chart-bar" style="color:var(--primary)" aria-hidden="true"></i>
                    Monthly Revenue — {{ $selectedYear }}
                </div>
                <div style="display:flex;align-items:center;gap:12px;font-size:12px;color:var(--text-muted)">
                    <span><span style="display:inline-block;width:10px;height:10px;background:var(--primary);border-radius:2px;margin-right:4px"></span>Paid</span>
                    <span><span style="display:inline-block;width:10px;height:10px;background:var(--success);border-radius:2px;margin-right:4px"></span>Current</span>
                    <span><span style="display:inline-block;width:10px;height:10px;background:var(--border);border-radius:2px;margin-right:4px"></span>Future</span>
                </div>
            </div>
            <div class="card-body">
                @php $maxBar = max(array_column($monthlyRevenue, 'amount')) ?: 1; @endphp
                <div class="bar-chart" id="mainBarChart">
                    @foreach($monthlyRevenue as $m)
                    @php
                        $h   = $m['is_future'] ? 2 : max(2, ($m['amount'] / $maxBar) * 100);
                        $cls = $m['is_future'] ? 'future-month' : ($m['is_current'] ? 'current-month' : 'normal-month');
                    @endphp
                    <div class="bar-col" title="{{ $m['month_full'] }}: ${{ number_format($m['amount'],2) }}">
                        <div class="bar-tooltip">
                            {{ $m['month_full'] }}<br>
                            ${{ number_format($m['amount'],2) }}
                            @if(!$m['is_future']) · {{ $m['count'] }} payments @endif
                        </div>
                        <div style="flex:1;display:flex;align-items:flex-end;width:100%">
                            <div class="bar-fill {{ $cls }}"
                                 data-h="{{ $h }}"
                                 style="height:2px">
                            </div>
                        </div>
                        <div class="bar-label">{{ $m['month_name'] }}</div>
                        <div class="bar-val">
                            @if($m['is_future'])
                                <span style="color:var(--text-muted)">—</span>
                            @else
                                ${{ $m['amount'] >= 1000 ? number_format($m['amount']/1000,1).'k' : number_format($m['amount'],0) }}
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Monthly breakdown table --}}
        <div class="card">
            <div class="card-header">
                <div class="card-title">Monthly Breakdown — {{ $selectedYear }}</div>
                <div style="font-size:13px;color:var(--text-muted)">
                    Total: <strong style="color:var(--text-primary)">${{ number_format($yearTotal,2) }}</strong>
                </div>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Month</th>
                            <th>Payments</th>
                            <th>Revenue</th>
                            <th>vs Expected</th>
                            <th style="width:160px">Progress</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($monthlyRevenue as $m)
                        @php
                            $pct = $expectedMonthly > 0 ? min(100, ($m['amount'] / $expectedMonthly) * 100) : 0;
                            $rowCls = $m['is_future'] ? 'future-row' : ($m['is_current'] ? 'current-row' : '');
                            $barCls = $m['is_future'] ? 'future' : ($m['is_current'] ? 'current' : '');
                        @endphp
                        <tr class="month-row {{ $rowCls }}">
                            <td>
                                <div style="display:flex;align-items:center;gap:8px">
                                    @if($m['is_current'])
                                        <span class="badge badge-success" style="font-size:10px">NOW</span>
                                    @elseif($m['is_future'])
                                        <span class="badge badge-gray" style="font-size:10px">—</span>
                                    @else
                                        <span class="badge badge-primary" style="font-size:10px">✓</span>
                                    @endif
                                    <span style="font-weight:600;color:var(--text-primary)">{{ $m['month_full'] }}</span>
                                </div>
                            </td>
                            <td style="color:var(--text-secondary)">
                                {{ $m['is_future'] ? '—' : $m['count'] }}
                            </td>
                            <td style="font-weight:700;color:{{ $m['is_future'] ? 'var(--text-muted)' : 'var(--text-primary)' }}">
                                {{ $m['is_future'] ? '—' : '$'.number_format($m['amount'],2) }}
                            </td>
                            <td style="font-size:12px;color:{{ $m['is_future'] ? 'var(--text-muted)' : ($pct >= 100 ? 'var(--success)' : ($pct >= 50 ? 'var(--warning)' : 'var(--danger)')) }}">
                                @if(!$m['is_future'])
                                    {{ number_format($pct,0) }}%
                                    @if($pct >= 100) <i class="fas fa-check-circle" aria-hidden="true"></i> @endif
                                @else
                                    —
                                @endif
                            </td>
                            <td>
                                <div class="progress-bar-wrap">
                                    <div class="progress-bar-fill {{ $barCls }}"
                                         data-w="{{ $m['is_future'] ? 0 : $pct }}"
                                         style="width:0%">
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr style="background:var(--bg-muted)">
                            <td style="font-weight:700;color:var(--text-heading)">Year Total</td>
                            <td style="font-weight:700;color:var(--text-primary)">{{ $yearPaidCount }}</td>
                            <td style="font-weight:900;color:var(--success);font-size:15px">${{ number_format($yearTotal,2) }}</td>
                            <td colspan="2" style="font-size:12px;color:var(--text-muted)">
                                Expected: ${{ number_format($expectedMonthly * 12, 2) }} / year
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

    </div>

    {{-- ── RIGHT: Sidebar panels ────────────────────────── --}}
    <div style="display:flex;flex-direction:column;gap:18px">

        {{-- 24-month trend --}}
        <div class="card">
            <div class="card-header">
                <div class="card-title">24-Month Trend</div>
            </div>
            <div class="card-body" style="padding-bottom:8px">
                @php $maxTrend = max(array_column($trendData, 'amount')) ?: 1; @endphp
                <div class="trend-chart" id="trendChart">
                    @foreach($trendData as $t)
                    @php
                        $th  = max(2, ($t['amount'] / $maxTrend) * 100);
                        $isCurrent = ($t['year'] === now()->year && $t['month'] === now()->month);
                    @endphp
                    <div class="trend-bar {{ $isCurrent ? 'current' : '' }}"
                         data-h="{{ $th }}"
                         style="height:2px"
                         title="{{ $t['label'] }}: ${{ number_format($t['amount'],2) }}">
                    </div>
                    @endforeach
                </div>
                {{-- X-axis labels: show every 6th --}}
                <div style="display:flex;gap:3px;margin-top:4px">
                    @foreach($trendData as $i => $t)
                    <div style="flex:1;font-size:9px;color:var(--text-muted);text-align:center;overflow:hidden">
                        {{ ($i % 6 === 0 || $i === count($trendData)-1) ? $t['label'] : '' }}
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Revenue by grade --}}
        <div class="card">
            <div class="card-header">
                <div class="card-title">By Grade — {{ $selectedYear }}</div>
            </div>
            <div class="card-body">
                @if($byGrade->isEmpty())
                    <div class="empty-state" style="padding:20px">
                        <i class="fas fa-chart-pie" aria-hidden="true"></i>
                        <p>No data for {{ $selectedYear }}</p>
                    </div>
                @else
                <div class="grade-grid">
                    @foreach($byGrade as $grade => $data)
                    <div class="grade-chip">
                        <div class="grade-chip-val">${{ $data['amount'] >= 1000 ? number_format($data['amount']/1000,1).'k' : number_format($data['amount'],0) }}</div>
                        <div class="grade-chip-label">Grade {{ $grade }}</div>
                        <div class="grade-chip-count">{{ $data['count'] }} payments</div>
                    </div>
                    @endforeach
                </div>

                {{-- Grade bar chart --}}
                @php $maxGrade = max(array_column($byGrade->toArray(), 'amount')) ?: 1; @endphp
                <div style="margin-top:16px;display:flex;flex-direction:column;gap:8px">
                    @foreach($byGrade as $grade => $data)
                    @php $gpct = ($data['amount'] / $maxGrade) * 100; @endphp
                    <div>
                        <div style="display:flex;justify-content:space-between;font-size:12px;margin-bottom:3px">
                            <span style="font-weight:600;color:var(--text-secondary)">Grade {{ $grade }}</span>
                            <span style="color:var(--text-muted)">${{ number_format($data['amount'],2) }}</span>
                        </div>
                        <div class="progress-bar-wrap">
                            <div class="progress-bar-fill"
                                 data-w="{{ $gpct }}"
                                 style="width:0%">
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>

        {{-- Collection rate this month --}}
        <div class="card">
            <div class="card-header">
                <div class="card-title">Collection Rate</div>
                <span style="font-size:11px;color:var(--text-muted)">{{ now()->format('F Y') }}</span>
            </div>
            <div class="card-body" style="text-align:center">
                @php
                    $rate = $expectedMonthly > 0 ? min(100, ($thisMonthAmount / $expectedMonthly) * 100) : 0;
                    $rateColor = $rate >= 80 ? 'var(--success)' : ($rate >= 50 ? 'var(--warning)' : 'var(--danger)');
                @endphp
                {{-- Circular progress --}}
                <div style="position:relative;display:inline-block;margin-bottom:12px">
                    <svg width="120" height="120" viewBox="0 0 120 120" aria-hidden="true">
                        <circle cx="60" cy="60" r="50" fill="none" stroke="var(--border)" stroke-width="10"/>
                        <circle cx="60" cy="60" r="50" fill="none"
                                stroke="{{ $rateColor }}" stroke-width="10"
                                stroke-linecap="round"
                                stroke-dasharray="{{ 314 }}"
                                stroke-dashoffset="{{ 314 - (314 * $rate / 100) }}"
                                transform="rotate(-90 60 60)"
                                style="transition:stroke-dashoffset 1s ease"/>
                    </svg>
                    <div style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);text-align:center">
                        <div style="font-size:22px;font-weight:900;color:{{ $rateColor }}">{{ number_format($rate,0) }}%</div>
                        <div style="font-size:10px;color:var(--text-muted)">collected</div>
                    </div>
                </div>
                <div style="font-size:13px;color:var(--text-secondary)">
                    <strong style="color:var(--text-primary)">${{ number_format($thisMonthAmount,2) }}</strong>
                    of
                    <strong style="color:var(--text-primary)">${{ number_format($expectedMonthly,2) }}</strong>
                    expected
                </div>
                @php $remaining = max(0, $expectedMonthly - $thisMonthAmount); @endphp
                @if($remaining > 0)
                <div style="font-size:12px;color:var(--text-muted);margin-top:6px">
                    ${{ number_format($remaining,2) }} still to collect
                </div>
                @else
                <div style="font-size:12px;color:var(--success);margin-top:6px;font-weight:600">
                    <i class="fas fa-check-circle" aria-hidden="true"></i> Target reached!
                </div>
                @endif
            </div>
        </div>

    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    // ── Animate bar chart ─────────────────────────────
    requestAnimationFrame(function () {
        document.querySelectorAll('.bar-fill[data-h]').forEach(function (el) {
            setTimeout(function () {
                el.style.height = el.dataset.h + '%';
            }, 50);
        });
        document.querySelectorAll('.trend-bar[data-h]').forEach(function (el) {
            setTimeout(function () {
                el.style.height = el.dataset.h + '%';
            }, 50);
        });
        document.querySelectorAll('.progress-bar-fill[data-w]').forEach(function (el) {
            setTimeout(function () {
                el.style.width = el.dataset.w + '%';
            }, 100);
        });
    });

    // ── Keyboard year navigation ──────────────────────
    document.addEventListener('keydown', function (e) {
        var sel = document.querySelector('select[name="year"]');
        if (!sel) return;
        if (e.key === 'ArrowLeft' && sel.selectedIndex < sel.options.length - 1) {
            sel.selectedIndex++;
            sel.form.submit();
        }
        if (e.key === 'ArrowRight' && sel.selectedIndex > 0) {
            sel.selectedIndex--;
            sel.form.submit();
        }
    });
});
</script>
@endsection
