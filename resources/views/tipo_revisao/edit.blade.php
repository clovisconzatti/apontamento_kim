@extends('layouts.model')

@section('content')
    @include('tipo_revisao._form', ['route' => '/tipo_revisao/edit/'.$tipo_revisao->id, 'type' => 'PATCH'])
@endsection
