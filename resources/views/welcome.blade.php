@extends('layouts.master') 
    @section('header')
    <h1>Dashboard</h1>
    @stop
    @section('content') 
      @include('partials.navigation')
      <!-- Left side column. contains the logo and sidebar -->
      @include('partials.home')  
@stop