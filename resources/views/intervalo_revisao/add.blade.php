@extends('layouts.model')

@section('content')
    @include('intervalo_revisao._form', ['route' => '/intervalo_revisao/store', 'type' => 'POST'])
@endsection
