@extends('layouts.model')

@section('content')
    @include('servico_revisao._form', ['route' => '/servico_revisao/edit/'.$servico->id, 'type' => 'PATCH'])
@endsection
