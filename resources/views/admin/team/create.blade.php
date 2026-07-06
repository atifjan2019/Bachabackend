@extends('layouts.admin')
@section('title', 'Add Team Member')
@section('content')

<div class="ph">
    <div>
        <h4>Add Team Member</h4>
        <div class="ph-sub">Add a card to the "Our Team Structure" section.</div>
    </div>
    <div class="ph-action">
        <a href="{{ route('admin.team.index') }}" class="btn btn-light">Cancel</a>
    </div>
</div>

<div class="bcard" style="max-width:680px;">
    <div class="card-body">
        <form action="{{ route('admin.team.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @include('admin.team._form', ['member' => null, 'icons' => $icons])
            <button type="submit" class="btn btn-primary mt-2"><i class="mdi mdi-account-plus"></i> Add Member</button>
        </form>
    </div>
</div>

@endsection
