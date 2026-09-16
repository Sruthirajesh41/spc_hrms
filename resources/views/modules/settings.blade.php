@extends('layouts.app')

@section('title', $module['title'])

@section('content')
    @include('partials.topbar', ['title' => $module['title'], 'eyebrow' => 'HR Management Module'])

    <div class="content">
        <div class="card" style="max-width:640px;">
            <h3>Policy & payroll configuration</h3>
            <p class="card-note">Every change here is written to the audit log.</p>
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
