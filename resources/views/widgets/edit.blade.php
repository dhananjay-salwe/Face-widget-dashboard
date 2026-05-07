@extends('layouts.app')

@section('content')
<h1 class="text-2xl font-bold mb-4">Edit Widget</h1>
@include('widgets._form', [
    'action' => route('widgets.update', $widget->id),
    'method' => 'PUT',
    'widget' => $widget
])
@endsection
