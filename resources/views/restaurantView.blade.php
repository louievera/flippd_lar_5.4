@extends('layouts.app')

@if(empty($result))
<h1>empty</h1>

@endif
@section('content')
<div class="container">
  <div class="row">
    <div class="form-group">
      <p>
        <b>Restaurant Name:</b>
        {{$result->name}}
      </p>
    </div>
    <div class="form-group">
      <p>
        <b>Claimer Name:</b>
        {{$result->claimer}}
      </p>
    </div>
    <div class="form-group">
      <p>
        <b>Description:</b>
        {{$result->description}}
      </p>
    </div>
    <div class="form-group">
      <p>
        <b>Address:</b>
        {{$result->address}}
      </p>
    </div>

    <div class="form-group">
      <p>
        <b>Email address:</b>
        {{$result->email}}
      </p>
    </div>
    <div class="form-group">
      <p>
        <b>Mobile Number:</b>
        {{$result->mobile_number}}
      </p>
    </div>

    <div class="form-group">
      <p>
        <b>Document:</b>
        <a href="https://docs.google.com/gview?url={!! asset('storage/restaurant_documents/'.$result->document)!!}" target="_blank" >
          View document
        </a>

        <!-- {{$result->document}} -->
      </p>
    </div>

    <div class="form-group">
      <p>
        <b>Verified:</b>

        {{$result->verified}}
      </p>
    </div>

    @if($result->verified == 'No')
      <div class="form-group">
        <p>
            <a href="../verifyOwner/{{$result->id}}/{{csrf_token()}}/{{ $result->business_id }}" class="btn btn-primary">
              Verify owner
            </a>
        </p>
      </div>
    @endif
    @endsection
