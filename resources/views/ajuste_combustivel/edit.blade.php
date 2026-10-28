@extends('layouts.model')

@section('content')
    @include('ajuste_combustivel._form', ['route' => '/ajuste_combustivel/edit/'.$ajuste->id, 'type' => 'PATCH'])
@endsection
