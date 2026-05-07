@extends('layouts.app')

@section('content')
<h1 class="text-2xl font-bold mb-4">Create Widget</h1>
@include('widgets._form', [
    'action' => route('widgets.store'),
    'method' => 'POST',
    'widget' => $widget
])
@endsection
