@extends('layouts.app')

@section('content')

<div class="container">

  @if(Session::has('success'))
  <div class="alert alert-success">
    <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
    <strong>Success!</strong> Ower verified!
  </div>
    @php
      Session::forget('success');
    @endphp
  @endif

  <div class="row">
    <table class='table'>
      <tr>
        <th>Restaurant</th>
        <th>Email</th>
        <th>Mobile</th>
        <th>Document</th>
        <th>Verified</th>
        <th></th>
      </tr>

      @foreach($results as $result)
      <tr>
        <td><b>{{ $result->name }}</b></td>
        <td>{{ $result->email }}</td>
        <td>{{$result->mobile_number}}</td>
        <td>{{$result->mobile_number}}</td>
        <td>{{$result->verified}}</td>
        <td>
          @if(isset($result->email))
            <a href="view/{{$result->id}}" class='btn {{$result->verified == "No" ? "btn-danger" : "btn-info"}}'>
                {{$result->verified == "No" ? "Verify" : "View" }}
            </a>
          @else
            No claimer
          @endif
        </td>
      </tr>
      @endforeach
    </table>
  </div>
  <div class='text-center'>
  {{ $results->render() }}
</div>
</div>
@endsection
