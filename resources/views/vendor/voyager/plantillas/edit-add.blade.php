@php
    $edit = !is_null($dataTypeContent->getKey());
    $add  = is_null($dataTypeContent->getKey());
    $miuser = Auth::user(); 
    $miwhats = App\Whatsapp::where("user_id", $miuser->id)->where("default" , true)->first();

    $mitamplate = App\Plantilla::find($dataTypeContent->getKey());

    $mifiles = App\Evento::where("user_id", $miuser->id)->where("tipo", "chat_multimedia")->orderBy("created_at", "desc")->take(300)->get();
    if(Auth::user()->role_id == 1){
        $contactos = App\Contacto::all();
        $grupos = App\Grupo::all();
    }else{
        $contactos = App\Contacto::where("user_id", $miuser->id)->get();
        $grupos = App\Grupo::where("bot", $miwhats->codigo)->get();
    }
    if($edit){
        // $mimulti = ($mitamplate->multimedia != '[]') ? (json_decode($mitamplate->multimedia))[0]->download_link : null;
        $mimulti = null;
        $migrupos = json_decode($mitamplate->grupos);
        $micontactos = json_decode($mitamplate->contactos);
    }else{
        $migrupos = array();
        $micontactos = array();
        $mimulti = null;
    }
    $files = Storage::disk('public')->files($miuser->name);
@endphp

@extends('voyager::master')

@section('css')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@stop

@section('page_title', __('voyager::generic.'.($edit ? 'edit' : 'add')).' '.$dataType->getTranslatedAttribute('display_name_singular'))

@section('page_header')
    <h1 class="page-title">
        <i class="{{ $dataType->icon }}"></i>
        {{ __('voyager::generic.'.($edit ? 'edit' : 'add')).' '.$dataType->getTranslatedAttribute('display_name_singular') }} | BOT: {{ $miwhats->nombre }} | TEL: {{ $miwhats->telefono }}
    </h1>
    @include('voyager::multilingual.language-selector')
@stop

@section('content')
    <div class="page-content edit-add container-fluid">
        <div class="row">
            <div class="col-md-8">

                <div class="panel panel-bordered">
                    <!-- form start -->
                    <form role="form"
                            class="form-edit-add"
                            action="{{ $edit ? route('voyager.'.$dataType->slug.'.update', $dataTypeContent->getKey()) : route('voyager.'.$dataType->slug.'.store') }}"
                            method="POST" enctype="multipart/form-data" id="miform">
                        <!-- PUT Method if we are editing -->
                        @if($edit)
                            {{ method_field("PUT") }}
                        @endif

                        <!-- CSRF TOKEN -->
                        {{ csrf_field() }}

                        <div class="panel-body">

                            @if (count($errors) > 0)
                                <div class="alert alert-danger">
                                    <ul>
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <!-- Adding / Editing -->
                            @php
                                $dataTypeRows = $dataType->{($edit ? 'editRows' : 'addRows' )};
                            @endphp

                            @foreach($dataTypeRows as $row)
                                <!-- GET THE DISPLAY OPTIONS -->
                                @php
                                    $display_options = $row->details->display ?? NULL;
                                    if ($dataTypeContent->{$row->field.'_'.($edit ? 'edit' : 'add')}) {
                                        $dataTypeContent->{$row->field} = $dataTypeContent->{$row->field.'_'.($edit ? 'edit' : 'add')};
                                    }
                                @endphp
                                @if (isset($row->details->legend) && isset($row->details->legend->text))
                                    <legend class="text-{{ $row->details->legend->align ?? 'center' }}" style="background-color: {{ $row->details->legend->bgcolor ?? '#f0f0f0' }};padding: 5px;">{{ $row->details->legend->text }}</legend>
                                @endif

                                <div class="form-group @if($row->type == 'hidden') hidden @endif col-md-{{ $display_options->width ?? 12 }} {{ $errors->has($row->field) ? 'has-error' : '' }}" @if(isset($display_options->id)){{ "id=$display_options->id" }}@endif>
                                    {{ $row->slugify }}
                                    <label class="control-label" for="name">{{ $row->getTranslatedAttribute('display_name') }}</label>
                                    @include('voyager::multilingual.input-hidden-bread-edit-add')
                                    @if ($add && isset($row->details->view_add))
                                        @include($row->details->view_add, ['row' => $row, 'dataType' => $dataType, 'dataTypeContent' => $dataTypeContent, 'content' => $dataTypeContent->{$row->field}, 'view' => 'add', 'options' => $row->details])
                                    @elseif ($edit && isset($row->details->view_edit))
                                        @include($row->details->view_edit, ['row' => $row, 'dataType' => $dataType, 'dataTypeContent' => $dataTypeContent, 'content' => $dataTypeContent->{$row->field}, 'view' => 'edit', 'options' => $row->details])
                                    @elseif (isset($row->details->view))
                                        @include($row->details->view, ['row' => $row, 'dataType' => $dataType, 'dataTypeContent' => $dataTypeContent, 'content' => $dataTypeContent->{$row->field}, 'action' => ($edit ? 'edit' : 'add'), 'view' => ($edit ? 'edit' : 'add'), 'options' => $row->details])
                                    @elseif ($row->type == 'relationship')
                                        @include('voyager::formfields.relationship', ['options' => $row->details])
                                    @else
                                        {!! app('voyager')->formField($row, $dataType, $dataTypeContent) !!}
                                    @endif
                                   
                                    @foreach (app('voyager')->afterFormFields($row, $dataType, $dataTypeContent) as $after)
                                        {!! $after->handle($row, $dataType, $dataTypeContent) !!}
                                    @endforeach
                                    @if ($errors->has($row->field))
                                        @foreach ($errors->get($row->field) as $error)
                                            <span class="help-block">{{ $error }}</span>
                                        @endforeach
                                    @endif
                                </div>
                            @endforeach
                            <!-- <div class="form-group col-md-6">                       
                                <div id="midiv"></div>
                            </div> -->
                            <!-- <h2>HASD</h2> -->
                        </div><!-- panel-body -->

                        <div class="panel-footer">
                            @section('submit-buttons')
                                <button type="submit" class="btn btn-primary save">{{ __('voyager::generic.save') }}</button>
                            @stop
                            @yield('submit-buttons')
                        </div>
                    </form>

                    <div style="display:none">
                        <input type="hidden" id="upload_url" value="{{ route('voyager.upload') }}">
                        <input type="hidden" id="upload_type_slug" value="{{ $dataType->slug }}">
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div id="midiv"></div>
            </div>                       

        </div>
    </div>

    <div class="modal fade modal-danger" id="confirm_delete_modal">
        <div class="modal-dialog">
            <div class="modal-content">

                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"
                            aria-hidden="true">&times;</button>
                    <h4 class="modal-title"><i class="voyager-warning"></i> {{ __('voyager::generic.are_you_sure') }}</h4>
                </div>

                <div class="modal-body">
                    <h4>{{ __('voyager::generic.are_you_sure_delete') }} '<span class="confirm_delete_name"></span>'</h4>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">{{ __('voyager::generic.cancel') }}</button>
                    <button type="button" class="btn btn-danger" id="confirm_delete">{{ __('voyager::generic.delete_confirm') }}</button>
                </div>
            </div>
        </div>
    </div>
    <!-- End Delete File Modal -->
 
@stop

@section('javascript')
    <script>
    
        var params = {};
        var $file;

        function deleteHandler(tag, isMulti) {
          return function() {
            $file = $(this).siblings(tag);

            params = {
                slug:   '{{ $dataType->slug }}',
                filename:  $file.data('file-name'),
                id:     $file.data('id'),
                field:  $file.parent().data('field-name'),
                multi: isMulti,
                _token: '{{ csrf_token() }}'
            }

            $('.confirm_delete_name').text(params.filename);
            $('#confirm_delete_modal').modal('show');
          };
        }

        $('document').ready(function () {
            $('.toggleswitch').bootstrapToggle();

            //Init datepicker for date fields if data-datepicker attribute defined
            //or if browser does not handle date inputs
            $('.form-group input[type=date]').each(function (idx, elt) {
                if (elt.hasAttribute('data-datepicker')) {
                    elt.type = 'text';
                    $(elt).datetimepicker($(elt).data('datepicker'));
                } else if (elt.type != 'date') {
                    elt.type = 'text';
                    $(elt).datetimepicker({
                        format: 'L',
                        extraFormats: [ 'YYYY-MM-DD' ]
                    }).datetimepicker($(elt).data('datepicker'));
                }
            });

            @if ($isModelTranslatable)
                $('.side-body').multilingual({"editing": true});
            @endif

            $('.side-body input[data-slug-origin]').each(function(i, el) {
                $(el).slugify();
            });

            $('.form-group').on('click', '.remove-multi-image', deleteHandler('img', true));
            $('.form-group').on('click', '.remove-single-image', deleteHandler('img', false));
            $('.form-group').on('click', '.remove-multi-file', deleteHandler('a', true));
            $('.form-group').on('click', '.remove-single-file', deleteHandler('a', false));

            $('#confirm_delete').on('click', function(){
                $.post('{{ route('voyager.'.$dataType->slug.'.media.remove') }}', params, function (response) {
                    if ( response
                        && response.data
                        && response.data.status
                        && response.data.status == 200 ) {

                        toastr.success(response.data.message);
                        $file.parent().fadeOut(300, function() { $(this).remove(); })
                    } else {
                        toastr.error("Error removing file.");
                    }
                });

                $('#confirm_delete_modal').modal('hide');
            });
            $('[data-toggle="tooltip"]').tooltip();
        });


        let migrupos = document.querySelector('select[name="grupos[]"]');
        @foreach($grupos as $item)
            var option = document.createElement("option");
            @if(in_array($item->codigo, $migrupos))
                option.value = "{{ $item->codigo }}";
                option.text = "{{ $item->name }} | {{ $item->type }}";
                option.selected = true
            @else
                var option = document.createElement("option");
                option.value = "{{ $item->codigo }}";
                option.text = "{{ $item->name }} | {{ $item->type }}";
            @endif
            migrupos.appendChild(option);
        @endforeach

        let micontactos = document.querySelector('select[name="contactos[]"]');
        @foreach($contactos as $item)
            var option = document.createElement("option");
            @if(in_array($item->codigo, $micontactos))
                option.value = "{{ $item->codigo }}";
                option.text = "{{ $item->name }} | {{ $item->number }}";
                option.selected = true
            @else
                var option = document.createElement("option");
                option.value = "{{ $item->codigo }}";
                option.text = "{{ $item->name }} | {{ $item->number }}";
            @endif
            micontactos.appendChild(option);
        @endforeach



        let mimensaje = document.querySelector('textarea[name="mensaje"]');
        mimensaje.rows = "12"
        let mifile = document.querySelector('select[name="multimedia"]');
        var option = document.createElement("option");
        option.text = "Elije un recurso de los ultimos {{ count($mifiles) }} registrados";
        mifile.append(option);
        mifile.focus()

        @if($add)
            let miname = document.querySelector('input[name="nombre"]');
            let newname = (Math.random() + 1).toString(36).substring(6);
            miname.value = newname

        @endif



        @if(isset($_GET['mifile']))
   
            option = document.createElement("option");
            var aux1 =  "{{ asset('storage/'.$_GET['mifile']) }}"
            var extension = aux1.substring(aux1.lastIndexOf('.') + 1);
            option.value = "{{ $_GET['mifile'] }}"
            option.text = "{{ $_GET['mifile'] }}"
            option.selected = true

            if ((extension == "mp4") || (extension == "ogg") ) {
                $("#midiv").html("<video controls class='chat-multimedia img-responsive'><source src='"+aux1+"'></video>")
            }else if(extension == "pdf"){
                $("#midiv").html("<iframe src='"+aux1+"' style='width:100%; height:400px;'></iframe>")
            } else {        
                $("#midiv").html("<img class='chat-multimedia img-responsive' src='"+aux1+"' />") 
            }
            mifile.append(option);
        @elseif($edit)
            option = document.createElement("option");
            var aux2 =  "{{ asset('storage/'.$mitamplate->multimedia) }}"
            console.log(aux2)
            var extension = aux2.substring(aux2.lastIndexOf('.') + 1);
            option.value = "{{ $mitamplate->multimedia }}"
            option.text = "{{ $mitamplate->multimedia }}"
            option.selected = true

            if ((extension == "mp4") || (extension == "ogg") ) {
                $("#midiv").html("<video controls class='chat-multimedia img-responsive'><source src='"+aux2+"'></video>")
            }else if(extension == "pdf"){
                $("#midiv").html("<iframe src='"+aux2+"' style='width:100%; height:400px;'></iframe>")
            } else {        
                $("#midiv").html("<img class='chat-multimedia img-responsive' src='"+aux2+"' />") 
            }
            mifile.append(option);

            @foreach($mifiles as $item)
                option = document.createElement("option");
                option.value = "{{ $item->file }}";
                option.text = "{{ $loop->index+1 }} | {{ $item->file }} | {{ $item->published }} "
                mifile.append(option);
            @endforeach
        @else
    
            @foreach($mifiles as $item)
                option = document.createElement("option");
                option.value = "{{ $item->file }}";
                option.text = "{{ $loop->index+1 }} | {{ $item->file }} | {{ $item->published }} "
                mifile.append(option);
            @endforeach
        @endif

        mifile.id = "miselec2"
        $('#miselec2').on('change', function (e) {
            // console.log("{{ asset('storage') }}/"+this.value)
            var fileName = "{{ asset('storage') }}/"+this.value
            var extension = fileName.substring(fileName.lastIndexOf('.') + 1);
            // console.log(extension)
            if ((extension == "mp4") || (extension == "ogg") ) {
                $("#midiv").html("<video controls autoplay class='chat-multimedia img-responsive'><source src='"+fileName+"'></video>")
            }else if(extension == "pdf"){
                $("#midiv").html("<iframe src='"+fileName+"' style='width:100%; height:600px;'></iframe>")
            } else {        
                $("#midiv").html("<img class='chat-multimedia img-responsive' src='"+fileName+"' />") 
            }
            mifile.focus()
        });

        $( "#miform" ).on( "submit", async function( event ) {
            // event.preventDefault()

            var midata = {
                grupos: Array.from(migrupos.selectedOptions).map(({ value }) => value),
                contactos: Array.from(micontactos.selectedOptions).map(({ value }) => value),
                bot: "{{ $miwhats->slug }}",
                codigo: "{{ $miwhats->codigo }}",
                message: mimensaje.value,
                multimedia: mifile.value            
            }
            await axios.post("{{ env('APP_BOT') }}/template", midata)
        });

    </script>
@stop
