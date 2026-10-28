@extends('layouts.model')

@section('content')
    @include('compra_combustivel._form', ['route' => '/compra_combustivel/store', 'type' => 'POST'])
@endsection
