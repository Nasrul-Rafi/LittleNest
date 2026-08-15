@extends('layouts.parent', ['title' => 'Edit Parent'])

@section('content')
<div class="page-header">
    <div>
        <h1>Edit Parent</h1>
        <p>Update account, contact and emergency details.</p>
    </div>
    <a class="button button-secondary" href="{{ route('admin.parents.show', $parent) }}">Back</a>
</div>

<form class="panel" method="POST" action="{{ route('admin.parents.update', $parent) }}">
    @csrf
    @include('admin.parents._form', ['parent' => $parent])
    <div class="form-actions">
        <button class="button" type="submit">Save Changes</button>
        <a class="button button-secondary" href="{{ route('admin.parents.show', $parent) }}">Cancel</a>
    </div>
</form>
@endsection
