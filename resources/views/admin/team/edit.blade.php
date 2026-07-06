@extends('layouts.admin')
@section('title', 'Edit Team Member')
@section('content')

<div class="ph">
    <div>
        <h4>Edit Team Member</h4>
        <div class="ph-sub">Update the <strong>{{ $member->title }}</strong> card.</div>
    </div>
    <div class="ph-action">
        <a href="{{ route('admin.team.index') }}" class="btn btn-light">Cancel</a>
    </div>
</div>

<div class="bcard" style="max-width:680px;">
    <div class="card-body">
        <form action="{{ route('admin.team.update', $member->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            @include('admin.team._form', ['member' => $member, 'icons' => $icons])
            <button type="submit" class="btn btn-primary mt-2"><i class="mdi mdi-content-save-outline"></i> Save Changes</button>
        </form>
    </div>
</div>

@endsection
