@extends('layouts.app')

@section('title', 'My Profile')

@section('content')
@include('partials.topbar', ['title' => 'My Profile', 'eyebrow' => 'HR Management Module'])

<div class="content">
    @include('partials.profile-card')
</div>

<script>
function hrTab(btn, name) {
    const card = btn.closest('.card');
    card.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
    card.querySelectorAll('.tabpanel').forEach(p => p.classList.remove('active'));
    btn.classList.add('active');
    card.querySelectorAll('[data-tabpanel="' + name + '"]').forEach(p => p.classList.add('active'));
}
</script>
@endsection