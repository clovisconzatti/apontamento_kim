@extends('layouts.model')

@section('content')
    @include('tipo_revisao._form', ['route' => '/tipo_revisao/store', 'type' => 'POST'])
@endsection
