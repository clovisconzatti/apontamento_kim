@extends('layouts.model')

@section('content')
    @include('compra_combustivel._form', ['route' => '/compra_combustivel/edit/'.$compra->id, 'type' => 'PATCH'])
@endsection
