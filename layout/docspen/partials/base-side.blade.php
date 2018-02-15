@extends('partials.base-footer')

@section('body-class', 'sidebar-layout')

@section('content')

    <div class="toolbar-container">
        <div class="faded-small toolbar">
            <div class="container fluid">
                <div class="row">
                    @yield('toolbar')
                </div>
            </div>
        </div>
    </div>

    <div class="flex-fill flex" @yield('container-attrs') >
        <div sidebar class="sidebar flex print-hidden" id="sidebar">
            <div class="sidebar-toggle primary-background-light"><i class="zmdi zmdi-caret-right-circle"></i>
            </div>
            <div class="scroll-body">
                @yield('sidebar')
            </div>
        </div>

        <div class="content flex">
            @yield('body')
        </div>
    </div>

@stop
