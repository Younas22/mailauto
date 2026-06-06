@extends('layouts.admin')

@section('title', 'Create Template')
@section('page-title', 'Create Template')
@section('page-subtitle', 'Build a new reusable email template')

@section('content')

{{-- Breadcrumb --}}
<nav class="flex items-center gap-2 text-sm text-slate-400 dark:text-slate-500 mb-6">
    <a href="{{ route('admin.dashboard') }}" class="hover:text-slate-600 dark:hover:text-slate-300 transition">Dashboard</a>
    <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
    </svg>
    <a href="{{ route('admin.templates.index') }}" class="hover:text-slate-600 dark:hover:text-slate-300 transition">Templates</a>
    <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
    </svg>
    <span class="text-slate-600 dark:text-slate-300 font-medium">Create</span>
</nav>

<form method="POST" action="{{ route('admin.templates.store') }}">
    @csrf
    @include('admin.templates._form')
</form>

@endsection
