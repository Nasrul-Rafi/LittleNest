@extends('layouts.parent', ['title' => 'Add Parent'])

@section('content')
<div class="page-header">
    <div>
        <h1>Add Parent</h1>
        <p>Create a parent account and contact profile.</p>
    </div>
    <a class="button button-secondary" href="{{ route('admin.parents.index') }}">Back</a>
</div>

<form class="panel" method="POST" action="{{ route('admin.parents.store') }}">
    @csrf
    @include('admin.parents._form')
    <div class="form-actions">
        <button class="button" type="submit">Create Parent</button>
        <a class="button button-secondary" href="{{ route('admin.parents.index') }}">Cancel</a>
    </div>
</form>
@endsection
