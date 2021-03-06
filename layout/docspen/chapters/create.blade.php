@extends('simple-layout')

@section('toolbar')
    <div class="col-sm-12 faded">
        <div class="breadcrumbs">
            <a href="{{$book->getUrl()}}" class="text-book text-button"><i class="zmdi zmdi-book"></i>{{ $book->getShortName() }}</a>
            <img src="https://cdnjs.cloudflare.com/ajax/libs/uswds/1.4.4/img/arrow-right.svg" style="height:10px">
            <a href="{{ baseUrl('/books/chapter/create') }}" class="text-button"><i class="zmdi zmdi-plus"></i>{{ trans('entities.chapters_create') }}</a>
        </div>
    </div>
@stop

@section('body')

    <div class="container small">
        <div class="card">
            <h3><i class="zmdi zmdi-plus"></i> {{ trans('entities.chapters_create') }}</h3>
            <div class="body">
                <form action="{{ $book->getUrl('/chapter/create') }}" method="POST">
                    @include('chapters.form')
                </form>
            </div>
        </div>
    </div>

@stop
