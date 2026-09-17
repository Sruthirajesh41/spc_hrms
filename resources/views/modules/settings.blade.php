@extends('layouts.app')

@section('title', $module['title'])

@section('content')
    @include('partials.topbar', [
        'title' => $module['title'],
        'eyebrow' => 'System',
        'heroIcon' => 'fa-solid fa-gear',
        'heroSummary' => 'Company profile and policy configuration — every change is audited.',
        'heroStats' => [
            ['label' => 'Settings', 'icon' => 'fa-solid fa-sliders', 'value' => count($settings)],
        ],
    ])

    <div class="content">
        <div class="card" style="max-width:680px;">
            <div class="widget-head">
                <div class="wh-ico"><i class="fa-solid fa-sliders"></i></div>
                <div>
                    <h3>Policy &amp; payroll configuration</h3>
                    <p>Every change here is written to the audit log.</p>
                </div>
            </div>
            <form method="POST" action="{{ route('settings.update') }}">
                @csrf
                <div class="field-grid">
                    @foreach($settings as $s)
                        <div class="field">
                            <label>{{ $s['label'] }}</label>
                            <input type="{{ $s['type'] }}" name="{{ $s['key'] }}" value="{{ old($s['key'], $s['value']) }}">
                        </div>
                    @endforeach
                </div>
                <div class="form-actions"><button type="submit" class="btn-primary">Save settings</button></div>
            </form>
        </div>


    </div>
@endsection
