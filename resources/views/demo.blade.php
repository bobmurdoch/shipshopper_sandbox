@php
    /**
     * @var array $states
     */
@endphp
@extends('layouts.app')
@section('content')
    <div id="app">
        <Demo
            :states="{{ \Illuminate\Support\Js::from($states) }}"
            address="1315 10th St"
            city="Sacramento"
            state="CA"
            zip="95814"
            country="US"
        ></Demo>
    </div>
@endsection
