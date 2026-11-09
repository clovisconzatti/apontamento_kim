@extends('layouts.model')

@section('content')
    @include('intervalo_revisao._form', ['route' => '/intervalo_revisao/edit/'.$intervalo->id, 'type' => 'PATCH'])
@endsection
