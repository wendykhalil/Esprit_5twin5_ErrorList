@extends('layouts.frontend')
@section('title', 'Modifier un relevé')
@section('content')<div class="min-h-screen bg-slate-50 px-4 py-10 sm:px-6 lg:px-8"><div class="mx-auto max-w-2xl"><div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8"><h1 class="text-2xl font-bold text-slate-900">Modifier le relevé</h1><form method="POST" action="{{ route('impact.update', $releve) }}" class="mt-6">@include('impact._form')</form></div></div></div>@endsection
