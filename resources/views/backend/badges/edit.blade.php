@extends('layouts.backend')
@section('content')<div class="mx-auto max-w-2xl rounded-xl border border-slate-200 bg-white p-6"><h2 class="text-2xl font-bold">Modifier un badge</h2><form class="mt-6" method="POST" enctype="multipart/form-data" action="{{ route('admin.badges.update', $badge) }}">@method('PUT')@include('backend.badges._form')</form></div>@endsection
