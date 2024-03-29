@extends('voyager::master')

@php
    $miwhats = App\Whatsapp::find($dataTypeContent->getKey());
    $micontactos = App\Contacto::where("user_id", Auth::user()->id)->get();
    $migrupos = App\Grupo::where("bot", $miwhats->codigo)->get();
    $mieventos = App\Evento::where("bot", $miwhats->codigo)->get();
    $sendcontacto = App\Contacto::where("bot", $miwhats->codigo)->where("send", false)->get();
    $sendgrupo = App\Grupo::where("bot", $miwhats->codigo)->where("send", false)->get();
    $miuser = App\Models\User::find(Auth::user()->id);
    $plantillaD = App\Plantilla::where("default", true)->first();

    App\Whatsapp::where('default', true)->where('user_id', Auth::user()->id)->update(['default' => false]);
    $miwhats->default = true;
    $miwhats->save();
    
    $miwhats = App\Whatsapp::find($dataTypeContent->getKey());
   
@endphp

@section('page_header')
    <link rel="stylesheet" href="{{ asset('styles/core.css') }}">
	<link rel="stylesheet" href="{{ asset('styles/style.css') }}">
	<link rel="stylesheet" href="{{ asset('styles/sidebar.css') }}">
	<link rel="stylesheet" href="{{ asset('styles/chat-window.css') }}">
	<link rel="stylesheet" href="{{ asset('styles/chat-tile.css') }}">
	<link rel="stylesheet" href="{{ asset('styles/chat-message.css') }}">
    <!-- <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" /> -->
@stop

@section('content')

    <main>
        <section id="chat-window">
            <header id="chat-window-header">
                <img src="" alt="" class="avatar" id="profile-image">
                <div id="active-chat-details">
                    <h2> BOT: {{ $miwhats->nombre }} | {{ $miwhats->telefono }}</h2>
                </div> 
            </header>

            <div class="container">
                <div class="row">
                    <div class="col-sm-8">
                        <div id="chat-window-contents">
                            <div id="misocket"></div>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">
                            <div class="panel panel-default">
                                <!-- <div class="panel-heading" role="tab" id="headingOne">
                                    <h4 class="panel-title">
                                        <a role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                        Opciones del Bot
                                        </a>
                                    </h4>
                                </div> -->
                                    <div id="collapseOne" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingOne">
                                    <div class="panel-body">
                                        
                                        <h4 class="text-center">INFORMACION</h4>
                                        <code>
                                            <!-- Nombre: {{ $miwhats->nombre }}
                                            <br> -->
                                            Default:  {{ $miwhats->default ? 'SI' : 'NO' }}
                                            <br>
                                            Activo:  {{ $miwhats->estado ? 'SI' : 'NO' }}
                                            <br>
                                            Codigo: {{ $miwhats->codigo }}
                                            <br>
                                            Telefono: {{ $miwhats->telefono }}            
                                            <br>
                                            Total Chats: {{ count($mieventos) }}
                                            <br>
                                            Total Grupos: {{ count($migrupos) }}
                                            <br>
                                            Total Contactos: {{ count($micontactos) }}
                                            <br>                                  
                                            Actualizado: {{ $miwhats->updated_at }}
                                        </code>
                                    
                                            <a href="#" id="miactivar" class="btn btn-primary btn-block btn-sm" onclick="activar()" >Iniciar Sesion</a>
                                            <!-- <br> -->
                                  
                                            <a href="#" id="miactivar" class="btn btn-danger btn-block btn-sm" onclick="stop()" >Eliminar Sesion</a>                  
                                

                                        <hr>
                                        <h4 class="text-center">CONSULTAS</h4>

                                        <a href="#" class="btn btn-dark btn-block btn-sm" onclick="michats()" >Todos los Chats</a>
                                        <a href="#" class="btn btn-dark btn-block btn-sm" onclick="miestados()" >Todos los Estados</a>
                                        <a href="#" class="btn btn-dark btn-block btn-sm" onclick="mijoinleave()" >E/S de grupos</a>
                                        <a href="#" class="btn btn-dark btn-block btn-sm" onclick="miprivate()" >Chats Privados</a>
                                        <hr>
                                        <h4 class="text-center">Herramientas</h4>
                                        <a href="/admin/descargas" class="btn btn-dark btn-block btn-sm" >Descargas</a>
                                        <a href="/admin/plantillas" class="btn btn-dark btn-block btn-sm" >Plantillas</a>
                                    </div>
                                </div>
                            </div>
                            
                        </div>
                    </div>
                </div>
            </div>
        </section>            
    </main>


@stop

@section('javascript')
    <script src="{{ asset('js/app.js') }}"></script>
    <script>
        $(document).ready(async function () { 
            var miwhats = await axios.post("/api/whatsapp/listar", {
                'bot': '{{ $miwhats->codigo }}'
            })
            listar(miwhats.data)
            @if($miwhats->estado)
                await axios.post("{{ env('APP_BOT') }}/historial?nombre={{ $miwhats->slug }}&codigo={{ $miwhats->codigo }}&user_id={{ Auth::user()->id }}")
            @endif
        });

                
        function sleep(ms) {
            return new Promise(
                resolve => setTimeout(resolve, ms)
            );
        }

        async function misend(type){
            
            switch (type) {
                case "contacto":
                    @foreach($sendcontacto as $item)                        
                        var segundos = Math.floor(Math.random() * 60) + 60;
                        console.log("{{ $item->codigo }}")
                        console.log("{{ $miwhats->codigo }}")
                        console.log(segundos)
                        await axios.post("{{ env('APP_BOT') }}/send?phone={{ $item->codigo }}&slug={{ $miwhats->slug }}&bot={{ $miwhats->codigo }}&type="+type) 
                        console.log("{{ $item->codigo }}")
                        await sleep(segundos * 1000)
                    @endforeach
                    break;
                case "grupo":
                    // console.log("{{ $sendgrupo }}")
                    @foreach($sendgrupo as $item)                        
                        var segundos = Math.floor(Math.random() * 60) + 60;
                        console.log("{{ $item->codigo }}")
                        console.log("{{ $miwhats->codigo }}")
                        console.log(segundos)
                        await axios.post("{{ env('APP_BOT') }}/send?phone={{ $item->codigo }}&slug={{ $miwhats->slug }}&bot={{ $miwhats->codigo }}&type="+type) 
                        console.log("{{ $item->codigo }}")
                        await sleep(segundos * 1000)
                    @endforeach
                    break;
                default:
                    break;
            }


        }

        async function activar(){
            $("#miactivar").hide()
            await axios.post("{{ env('APP_BOT') }}/init", {
                nombre: "{{ $miwhats->slug }}",
                codigo: "{{ $miwhats->codigo }}",
                user: "{{ $miuser->name }}"
            })         

        }
              
        async function stop(){
            var mirest = await axios.post("{{ env('APP_BOT') }}/stop?nombre={{ $miwhats->slug }}&codigo={{ $miwhats->codigo }}") 
            // $("#miactivar").hide()
            location.reload()
        }

        window.Echo.channel('messages')
            .listen('MiEvent', async (e) => {  
                var miwhats = e.message
                console.log(e.message)
                if ( ("{{ $miwhats->default }}" == 1) && (miwhats.user_id == "{{ Auth::user()->id }}") ) {
                    var milink = "{{ asset('storage') }}" 
                    milink = milink+"/"+miwhats.file
                    $("#misocket").prepend("<hr style='border-top: 1px solid #2D353E;'>")
                                        
                    var miauthor = miwhats.miauthor ? miwhats.miauthor.name : miwhats.author;
                    var migrupo = miwhats.grupo ? miwhats.grupo.name : miwhats.desde
                    var micontacto = miwhats.contacto ? miwhats.contacto.name : miwhats.desde
                    var mifecha =  miwhats.published
                    if (miwhats.mensaje) {
                        var messages = miwhats.mensaje
                        // for(var i=0; i< messages.length; i++) {
                        //     messages = messages.replace(/\~(.*)\~/, "<del>$1</del>")
                        //         .replace(/\_(.*)\_/, "<em>$1</em>")
                        //         .replace(/\*(.*)\*/, "<strong>$1</strong>")
                        // }
                        // messages = messages.replace(/(\b(https?|):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|])/ig, "<a href='$1' target='_blank'>$1</a>");
                        $("#misocket").prepend("<div class='chat-message-group'><div class='chat-message'>"+messages+"</div></div>") 
                    }

                    switch (miwhats.tipo) {
                        case "chat_multimedia":    
                            // $("#misocket").prepend("<div class='chat-message-group'><div class='chat-message'>"+miwhats.fwhats+"</div></div>")   
                            $("#misocket").prepend("<a href='/admin/plantillas/create?mifile="+miwhats.file+"' class='btn btn-sm btn-success'>Enviar con el bot</a>")                             
                            if(miwhats.extension == "video/mp4") {
                                $("#misocket").prepend("<video controls class='chat-multimedia'><source src='"+milink+"' type='"+miwhats.extension+"'></video>")
                            } else if(miwhats.extension == "audio/ogg; codecs=opus") {
                                $("#misocket").prepend("<audio controls><source src='"+milink+"' type='"+miwhats.extension+"'></audio>")
                            } else if(miwhats.extension == "audio/mp4") {
                                $("#misocket").prepend("<audio controls><source src='"+milink+"' type='"+miwhats.extension+"'></audio>")
                            } else if(miwhats.extension == "application/zip") {
                                $("#misocket").prepend("<a href='/storage/"+miwhats.file+"' class='btn btn-dark'>Descargar el ZIP</a>")
                            } else if(miwhats.extension == "application/pdf") {
                                $("#misocket").prepend("<iframe src='/storage/"+miwhats.file+"' style='width:100%; height:400px;'></iframe>")
                            } else {                                
                                $("#misocket").prepend("<img class='chat-multimedia' src='"+milink+"' />")   
                            }   

                            switch (miwhats.subtipo) {
                                case 'chat_private':
                                    // var micontacto = miwhats.contacto ? miwhats.contacto.name : miwhats.desde
                                    $("#misocket").prepend("<div class='datestamp-container'><span class='datestamp'>"+miwhats.clase+" | "+micontacto+" | "+mifecha+"</span></div>")
                                    break;
                                case 'chat_group':
                                    // var miauthor = miwhats.miauthor ? miwhats.miauthor.name : miwhats.author;
                                    // var migrupo = miwhats.grupo ? miwhats.grupo.name : miwhats.desde
                                    $("#misocket").prepend("<div class='datestamp-container'><span class='datestamp'>"+miwhats.clase+" | "+migrupo+" | "+miauthor+" | "+mifecha+"</span></div>")
                                    break;
                                case 'status':
                                    // var micontacto = miwhats.contacto ? miwhats.contacto.name : miwhats.desde
                                    $("#misocket").prepend("<div class='datestamp-container micontext'><span class='datestamp'>"+miwhats.clase+" | "+micontacto+" | "+mifecha+"</span></div>")
                                    break;
                                default:
                                    break;
                            }  
                            break;
                        case "chat_location":
                            switch (miwhats.subtipo) {
                                case 'chat_private':
                                    //   var micontacto = miwhats.contacto ? miwhats.contacto.name : miwhats.desde
                                    $("#misocket").prepend("<div class='datestamp-container'><span class='datestamp'>"+micliente+" | "+miwhats.fwhats+"</span></div>")                              
                                    break;
                                case 'chat_group':
                                    // var miauthor = miwhats.miauthor ? miwhats.miauthor.name : miwhats.author;
                                    // var migrupo = miwhats.grupo ? miwhats.grupo.name : miwhats.desde
                                    $("#misocket").prepend("<div class='datestamp-container'><span class='datestamp'>"+migrupo+" | "+miauthor+" | "+miwhats.fwhats+"</span></div>")
                                    break;
                                default:
                                    break;
                            }   
                            var milanlog = JSON.parse(miwhats.datos)
                            $("#misocket").prepend("<img style='width: 40%' src='"+milink+"' />")
                            $("#misocket").prepend("<div class='chat-message-group'><div class='chat-message'><a href='https://maps.google.com/?ll="+milanlog.latitude+","+milanlog.longitude+"' target='_blank'>? IR AL MAPA</a><span class='chat-message-time'>"+miwhats.published+"</span></div></div>")                                
                            break;
                        case "chat_private":            
                                // var micontacto = miwhats.contacto ? miwhats.contacto.name : miwhats.desde
                                $("#misocket").prepend("<div class='datestamp-container'><span class='datestamp'>"+miwhats.clase+" | "+micontacto+" | "+mifecha+"</span></div>")
                            break;
                        case "chat_group":
                            // var miauthor = miwhats.miauthor ? miwhats.miauthor.name : miwhats.author;
                            // var migrupo = miwhats.grupo ? miwhats.grupo.name : miwhats.desde
                            $("#misocket").prepend("<div class='datestamp-container'><span class='datestamp'>"+miwhats.clase+" | "+migrupo+" | "+miauthor+" | "+mifecha+"</span></div>")
                            break;
                        case "qr":            
                            $("#misocket").prepend("<img class='chat-multimedia' src='"+milink+"' />") 
                            break;
                        case "ready":            
                            location.reload()
                        case "destroy":            
                            location.reload()
                            break;  
                        case "join":
                            // var miauthor = miwhats.miauthor ? miwhats.miauthor.name : miwhats.author;
                            // var migrupo = miwhats.grupo ? miwhats.grupo.name : miwhats.desde
                            $("#misocket").prepend("<div class='datestamp-container micontext'><span class='datestamp'>"+migrupo+" | "+miauthor+" | "+mifecha+"</span></div>")
                            break;
                        case "leave":
                            // var miauthor = miwhats.miauthor ? miwhats.miauthor.name : miwhats.author;
                            // var migrupo = miwhats.grupo ? miwhats.grupo.name : miwhats.desde
                            $("#misocket").prepend("<div class='datestamp-container micontext'><span class='datestamp'>"+migrupo+" | "+miauthor+" | "+mifecha+"</span></div>")
                            break;                           
                        default:
                            break;
                    }                                  
                }
            }
        )


        // async function micontactos(){
        //     $("#mibtn").hide()
        //     await axios.post("{{ env('APP_BOT') }}/contactos" , {
        //         nombre: "{{ $miwhats->slug }}",
        //         codigo: "{{ $miwhats->codigo }}",
        //         user_id: "{{ Auth::user()->id }}",
        //         user: "{{ $miuser->name }}"
        //     })
        //     // location.reload()
        // }

        // async function migrupos(){
        //     $("#migrupobtn").hide()
        //     await axios.post("{{ env('APP_BOT') }}/historial?nombre={{ $miwhats->slug }}&codigo={{ $miwhats->codigo }}&user_id={{ Auth::user()->id }}")
        //     // location.reload()
        // }

        async function miestados(){
            toastr.info('Solicitud enviada..');
            var miwhats = await axios.post("/api/whatsapp/estados", {
                'codigo': '{{ $miwhats->codigo }}'
            })
            listar(miwhats.data)
        }

        async function michats(){
            toastr.info('Solicitud enviada..');
            var miwhats = await axios.post("/api/whatsapp/chats", {
                'codigo': '{{ $miwhats->codigo }}'
            })
            // console.log(miwhats.data)
            listar(miwhats.data)
        }
        async function mijoinleave(){
            toastr.info('Solicitud enviada..');
            var miwhats = await axios.post("/api/whatsapp/joinleave", {
                'codigo': '{{ $miwhats->codigo }}'
            })
            listar(miwhats.data)
        }
        
        async function miprivate(){
            toastr.info('Solicitud enviada..');
            var miwhats = await axios.post("/api/whatsapp/private", {
                'codigo': '{{ $miwhats->codigo }}'
            })
            listar(miwhats.data)
        }

        // async function migrupo(){
        //     var miwhats = await axios.post("/api/whatsapp/grupo", {
        //         'whatsapp': '{{ $miwhats->codigo }}'
        //     })
        //     listar(miwhats.data)
        // }
        // async function migrupo2(){
        //     var miwhats = await axios.post("/api/whatsapp/grupo2", {
        //         'whatsapp': '{{ $miwhats->codigo }}'
        //     })
        //     listar(miwhats.data)
        // }

        async function listar(miwhats) {
            $("#misocket").html("")  
            for (let index = 0; index < miwhats.length; index++) {
                var milink = "{{ asset('storage') }}" 
                milink = milink+"/"+miwhats[index].file
                
                var micontacto = miwhats[index].contacto ? miwhats[index].contacto.name : miwhats[index].desde
                var miauthor = miwhats[index].miauthor ? miwhats[index].miauthor.name : miwhats[index].author;
                var migrupo = miwhats[index].grupo ? miwhats[index].grupo.name : miwhats[index].desde
                var minumber = miwhats[index].contacto ? miwhats[index].contacto.number : null
                var mifecha =  miwhats[index].published
                // console.log(miwhats[index])
                switch (miwhats[index].tipo) {
                    case "chat_multimedia":
                        // console.log(miwhats[index])
                        switch (miwhats[index].subtipo) {                            
                            case 'chat_private':
                                // var micontacto = miwhats[index].contacto ? miwhats[index].contacto.name : miwhats[index].desde
                                $("#misocket").append("<div class='datestamp-container micontext'><span class='datestamp'>"+miwhats[index].clase+" | "+minumber+" | "+micontacto+" | "+mifecha+"</span></div>")
                                break;
                            case 'chat_group':
                                    // var miauthor = miwhats[index].miauthor ? miwhats[index].miauthor.name : miwhats[index].author;
                                    // var migrupo = miwhats[index].grupo ? miwhats[index].grupo.name : miwhats[index].desde
                                    $("#misocket").append("<div class='datestamp-container micontext'><span class='datestamp'>"+miwhats[index].clase+" | "+migrupo+" | "+miauthor+" | "+mifecha+"</span></div>")
                                break;
                            case 'status':
                                // var micontacto = miwhats[index].contacto ? miwhats[index].contacto.name : miwhats[index].desde
                                $("#misocket").append("<div class='datestamp-container micontext'><span class='datestamp'>"+miwhats[index].clase+" | "+minumber+" | "+micontacto+" | "+mifecha+"</span></div>")
                                break;
                            default:
                                break;
                        }
                                                                      
                        if(miwhats[index].extension == "video/mp4") {
                            $("#misocket").append("<video controls class='chat-multimedia'><source src='"+milink+"' type='"+miwhats[index].extension+"'></video>")                           
                        } else if(miwhats[index].extension == "audio/ogg; codecs=opus") {
                            $("#misocket").append("<audio controls><source src='"+milink+"' type='"+miwhats[index].extension+"'></audio>")
                        } else if(miwhats[index].extension == "audio/mp4") {
                            $("#misocket").append("<audio controls><source src='"+milink+"' type='"+miwhats[index].extension+"'></audio>")
                        } else if(miwhats[index].extension == "application/zip") {
                            $("#misocket").append("<a href='/storage/"+miwhats[index].file+"' class='btn btn-dark'>Descargar el ZIP</a>")
                        } else if(miwhats[index].extension == "application/pdf") {
                            $("#misocket").append("<iframe src='/storage/"+miwhats[index].file+"' style='width:100%; height:400px;'></iframe>")
                        } else {
                            $("#misocket").append("<img class='chat-multimedia' src='"+milink+"' />")   
                        }   

                        $("#misocket").append("<a href='/admin/plantillas/{{ $plantillaD->id }}/edit?mifile="+miwhats[index].file+"' class='btn btn-sm btn-success'>Enviar con el bot</a>")
                        // $("#misocket").append("<div class='chat-message-group'><div class='chat-message'>"+miwhats[index].fwhats+"</div></div>")     
                        break;
                    case "chat_location":
                        switch (miwhats[index].subtipo) {
                            case 'chat_private':
                                var micontacto = miwhats[index].contacto ? miwhats[index].contacto.name : miwhats[index].desde                                    
                                $("#misocket").append("<div class='datestamp-container'><span class='datestamp'>"+micontacto+" | "+miwhats[index].fwhats+"</span></div>")                   
                                break;
                            case 'chat_group':
                                var miauthor = miwhats[index].miauthor ? miwhats[index].miauthor.name : miwhats[index].author;
                                    var migrupo = miwhats[index].grupo ? miwhats[index].grupo.name : miwhats[index].desde
                                $("#misocket").append("<div class='datestamp-container'><span class='datestamp'>"+migrupo+" | "+miauthor+" | "+miwhats[index].fwhats+"</span></div>")
                                break;
                            default:
                                break;
                        }   
                        var milanlog = JSON.parse(miwhats[index].datos)
                        $("#misocket").append("<img style='width: 40%' src='"+milink+"' />")
                        $("#misocket").append("<div class='chat-message-group'><div class='chat-message'><a href='https://maps.google.com/?ll="+milanlog.latitude+","+milanlog.longitude+"' target='_blank'>? IR AL MAPA</a><span class='chat-message-time'>"+miwhats[index].published+"</span></div></div>")                                                     
                        break;
                    case "chat_private":             
                        // var micontacto = miwhats[index].contacto ? miwhats[index].contacto.name : miwhats[index].desde                                    
                        $("#misocket").append("<div class='datestamp-container'><span class='datestamp'>"+miwhats[index].clase+" | "+miwhats[index].id+" | "+micontacto+" | "+mifecha+"</span></div>")
                        break;
                    case "chat_group":
                        // var miauthor = miwhats[index].miauthor ? miwhats[index].miauthor.name : miwhats[index].author;
                        // var migrupo = miwhats[index].grupo ? miwhats[index].grupo.name : miwhats[index].desde
                        $("#misocket").append("<div class='datestamp-container micontext'><span class='datestamp'>"+miwhats[index].clase+" | "+migrupo+" | "+miauthor+" | "+mifecha+"</span></div>")
                        break;
                    case "join":
                        // var miauthor = miwhats[index].miauthor ? miwhats[index].miauthor.name : miwhats[index].author;
                        // var migrupo = miwhats[index].grupo ? miwhats[index].grupo.name : miwhats[index].desde
                        $("#misocket").append("<div class='datestamp-container micontext'><span class='datestamp'>"+migrupo+" | "+miauthor+" | "+mifecha+"</span></div>")
                        break;
                    case "leave":
                        // var miauthor = miwhats[index].miauthor ? miwhats[index].miauthor.name : miwhats[index].author;
                        // var migrupo = miwhats[index].grupo ? miwhats[index].grupo.name : miwhats[index].desde
                        $("#misocket").append("<div class='datestamp-container micontext'><span class='datestamp'>"+migrupo+" | "+miauthor+" | "+mifecha+"</span></div>")
                        break;
                    default:
                        break;
                }

                if (miwhats[index].mensaje) {
                    var messages = miwhats[index].mensaje
                    // for(var i=0; i< messages.length; i++) {
                    //     messages = messages.replace(/\~(.*)\~/, "<del>$1</del>")
                    //         .replace(/\_(.*)\_/, "<em>$1</em>")
                    //         .replace(/\*(.*)\*/, "<strong>$1</strong>")
                    // }
                    // messages = messages.replace(/(\b(https?|):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|])/ig, "<a href='$1' target='_blank'>$1</a>");
                    // $("#misocket").append("<div class='chat-message-group text-center'><div class='chat-message'>"+messages+"<span class='chat-message-time'>"+mifecha+"</span></div></div>") 
                    $("#misocket").append("<div class='chat-message-group text-center'><div class='chat-message'>"+messages+"</div></div>") 


                }
                $("#misocket").append("<hr style='border-top: 1px solid #2D353E;'>")
            }
            $("#misocket").prepend("<hr style='border-top: 1px solid #2D353E;'>")
            $("#misocket").prepend("<div class='chat-message-group'><div class='chat-message'>Mostrando los ultimos "+miwhats.length+" registros</div></div>") 

        }
                                                                   
    </script>
@stop