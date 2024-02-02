const qrcode = require('qrcode-terminal');
const axios = require('axios');
var qr = require('qr-image');
var path = require('path');
const { Client, LocalAuth, MessageMedia, Poll, List} = require("whatsapp-web.js");
require('dotenv').config({ path: '../../.env' })
const fs = require("fs");
const cors = require('cors')
const express = require('express');
var sessionstorage = require('sessionstorage');

const YTDlpWrap = require('yt-dlp-wrap').default;
const ytDlpWrap = new YTDlpWrap('/usr/bin/yt-dlp');

const Replicate = require('replicate');
const replicate = new Replicate({
    auth: 'r8_c2kcZYqf15XThwYq0DNqulkwrbe31Pa29c3kA',
  });


const app = express();
app.use(express.json())
app.use(cors())


app.listen(process.env.API_PORT, async () => {
    sessionstorage.clear()
    await axios.post(process.env.APP_API+'reset')
    console.log('iniciando el serverbot...')
    console.log('CHATBOT ESTA LISTO EN EL PUERTO: '+process.env.API_PORT);
});

app.get('/', async (req, res) => {
    res.send('CHATBOT ESTA LISTO EN EL PUERTO: '+process.env.API_PORT)
});

app.post('/init', async (req, res) => {
    console.log(req.body)

    if (!fs.existsSync('../storage/'+req.body.user)){
        fs.mkdirSync('../storage/'+req.body.user);
    }

    const wbot = new Client({
        authStrategy: new LocalAuth({
            clientId: req.body.nombre
        }),
        puppeteer: {
            executablePath: '/usr/bin/google-chrome-stable',
            args: ['--no-sandbox']
        }
    });
    
    wbot.on('qr', async (qrwb) => {
        console.log('-----------------'+req.body.nombre+'----------------')
        let r = (Math.random() + 1).toString(36).substring(3);
        var qr_svg = qr.image(qrwb, { type: 'png' });
        qr_svg.pipe(require('fs').createWriteStream('../storage/'+req.body.user+'/'+r+'.png'));

        await axios.post(process.env.APP_API+'evento', {
            'mensaje': 'Escanea el nuevo QR',
            'tipo': 'qr',
            'bot': req.body.codigo,
            'file': req.body.user+'/'+r+'.png',

        })
    });

    wbot.on("authenticated", async session => {
        console.log('authenticated');
        // console.log('-----------------authenticated-----------------')
        try {
            await axios.post(process.env.APP_API+'estado', {
                'whatsapp': req.body.codigo,
                'estado': true
            })
        } catch (error) {
            console.log(error)
        }

    });
    
    wbot.on('auth_failure', async msg => {
        // Fired if session restore was unsuccessful
        console.error('AUTHENTICATION FAILURE', msg);
        wbot.destroy()
        sessionstorage.removeItem(req.body.nombre)
        await axios.post(process.env.APP_API+'estado', {
            'bot': req.body.codigo,
            'estado': false
        })
        await axios.post(process.env.APP_API+'evento', {
            'clase': 'input',
            'tipo': 'destroy',
            'bot': req.body.codigo,
            'mensaje': 'El bot '+req.body.nombre+', fue eliminado'
        })
        fs.rmSync('.wwebjs_auth/session-'+req.body.nombre, { recursive: true, force: true }); 
    });

    wbot.on('ready', async () => {
        console.log('-----------------ready-----------------')
        try {
            await axios.post(process.env.APP_API+'evento', {
                'mensaje': 'Bot '+req.body.nombre+' esta linea',
                'tipo': 'ready',
                'bot': req.body.codigo
            })
            await axios.post(process.env.APP_API+'estado', {
                'bot': req.body.codigo,
                'estado': true
            })
            sessionstorage.setItem(req.body.nombre, wbot)
        } catch (error) {
            console.log(error)
        }
    });

    wbot.on('message', async (msg) => {
        const chat = await msg.getChat();
        var mitipo = null
        var miauthor = null
        if (!fs.existsSync('../storage/'+req.body.user)){
            fs.mkdirSync('../storage/'+req.body.user);
        }
        console.log(req.body)
        try {
            if (msg.from != "status@broadcast") {                       
                var misubtype = chat.lastMessage ? chat.lastMessage._data.subtype : null 
                if(chat.isGroup) {
                    mitipo = 'chat_group'
                    miauthor = chat.lastMessage ? chat.lastMessage.author : null
                }else{       
                    mitipo = 'chat_private'
                    miauthor = null
                }
        
                if(msg.hasMedia) {    
                    const media = await msg.downloadMedia(); 
                    if (media) {           
                            
                        let r = (Math.random() + 1).toString(36).substring(3);
                        let mifile = null   
                        
                        const imgBuffer = Buffer.from(media.data, 'base64');
  
                        switch (media.mimetype) {
                            case 'image/jpeg':
                                fs.writeFileSync('../storage/'+req.body.user+'/'+r+'.jpeg', imgBuffer);
                                mifile = req.body.user+'/'+r+'.jpeg'
                                break;
                            case 'image/webp':
                                fs.writeFileSync('../storage/'+req.body.user+'/'+r+'.webp', imgBuffer);
                                mifile = req.body.user+'/'+r+'.webp'
                                break;
                            case 'video/mp4':
                                fs.writeFileSync('../storage/'+req.body.user+'/'+r+'.mp4', imgBuffer);
                                mifile = req.body.user+'/'+r+'.mp4'
                                break;
                            case 'audio/ogg; codecs=opus':
                                fs.writeFileSync('../storage/'+req.body.user+'/'+r+'.ogg', imgBuffer);
                                mifile = req.body.user+'/'+r+'.ogg'
                                break;
                            case 'audio/mp4':
                                fs.writeFileSync('../storage/'+req.body.user+'/'+r+'.mp4', imgBuffer);
                                mifile = req.body.user+'/'+r+'.mp4'
                                break;
                            case 'application/zip':
                                fs.writeFileSync('../storage/'+req.body.user+'/'+r+'.zip', imgBuffer);
                                mifile = req.body.user+'/'+r+'.zip'
                                break;
                            case 'application/pdf':
                                fs.writeFileSync('../storage/'+req.body.user+'/'+r+'.pdf', imgBuffer);
                                mifile = req.body.user+'/'+r+'.pdf'
                                break;
                            default:
                                
                                break;
                        }
                
                        await axios.post(process.env.APP_API+'evento', {
                            'clase': 'input',
                            'mensaje': msg.body,
                            'tipo': 'chat_multimedia',
                            'bot': req.body.codigo,
                            'desde': msg.from,
                            'file': mifile,
                            'extension': media.mimetype,
                            'subtipo': mitipo,
                            'author': miauthor,
                            'subtype': misubtype,
                            'whatsapp': msg.timestamp,
                            'datos': msg
                        })
                    }
                }else if(msg.location){
                    const imgBuffer = Buffer.from(msg.body, 'base64');
                    const r = (Math.random() + 1).toString(36).substring(3);
                    fs.writeFileSync('../storage/'+req.body.user+'/'+r+'.jpeg', imgBuffer);
                    var mifile = req.body.user+'/'+r+'.jpeg'
                    await axios.post(process.env.APP_API+'evento', {
                        'clase': 'input',
                        'tipo': 'chat_location',
                        'datos': msg.location,
                        'bot': req.body.codigo,
                        'desde': msg.from,
                        'file': mifile,
                        'extension': 'image/jpeg',
                        'subtipo': mitipo,
                        'whatsapp': msg.timestamp
                    })
                }else{
                    await axios.post(process.env.APP_API+'evento', {
                        'clase': 'input',
                        'mensaje': msg.body,
                        'tipo': mitipo,
                        'bot': req.body.codigo,
                        'desde': msg.from,
                        'author': miauthor,
                        'subtype': misubtype,
                        'whatsapp': msg.timestamp,
                        'datos': msg
                    })
                }
            }


            if(msg.fromMe){
                console.log(msg)
            }
        } catch (error) {
            console.log(error)   
            await axios.post(process.env.APP_API+'evento', {
                'clase': 'input',
                'tipo': 'error',
                'bot': req.body.codigo,
                'desde': msg.from,
                'whatsapp': msg.timestamp,
                'mensaje': error,
            })
        }
    });

    wbot.on('message_create', async (msg) => {
     
        try { 
            if (msg.from == "status@broadcast") {
                if (msg.hasMedia ) {                        
                    const media = await msg.downloadMedia(); 
                    if (media) {   
                        let r = (Math.random() + 1).toString(36).substring(3);
                        let mifile = null  
                        const imgBuffer = Buffer.from(media.data, 'base64');
                        switch (media.mimetype) {
                            case 'image/jpeg':
                                fs.writeFileSync('../storage/'+req.body.user+'/'+r+'.jpeg', imgBuffer);
                                mifile = req.body.user+'/'+r+'.jpeg'
                                break;
                            case 'image/webp':
                                fs.writeFileSync('../storage/'+req.body.user+'/'+r+'.webp', imgBuffer);
                                mifile = req.body.user+'/'+r+'.webp'
                                break;
                            case 'video/mp4':
                                fs.writeFileSync('../storage/'+req.body.user+'/'+r+'.mp4', imgBuffer);
                                mifile = req.body.user+'/'+r+'.mp4'
                                break;
                            default:
                                break;
                        }
                        await axios.post(process.env.APP_API+'evento', {
                            'clase': 'input',
                            'mensaje': msg.body,
                            'tipo': 'chat_multimedia',
                            'subtipo': 'status',
                            'file': mifile,
                            'bot': req.body.codigo,
                            'desde': msg.author,
                            'author': msg.author,
                            'whatsapp': msg.timestamp,
                            'extension': media.mimetype
                        })
                    }
                }else{
                    // console.log(msg)
                }
            }else if(msg.fromMe){
                console.log("para mi")
            }

            //--------------- misms ---------------
            if (msg.fromMe) {
                if (msg.hasMedia ) {                        
                    const media = await msg.downloadMedia(); 
                    let r = (Math.random() + 1).toString(36).substring(3);
                    let mifile = null            
                    var mimediadata = media ? media.data : null
                    const imgBuffer = Buffer.from(mimediadata, 'base64');
                    switch (media.mimetype) {
                        case 'image/jpeg':
                            fs.writeFileSync('../storage/'+req.body.user+'/'+r+'.jpeg', imgBuffer);
                            mifile = req.body.user+'/'+r+'.jpeg'
                            break;
                        case 'image/webp':
                            fs.writeFileSync('../storage/'+req.body.user+'/'+r+'.webp', imgBuffer);
                            mifile = req.body.user+'/'+r+'.webp'
                            break;
                        case 'video/mp4':
                            fs.writeFileSync('../storage/'+req.body.user+'/'+r+'.mp4', imgBuffer);
                            mifile = req.body.user+'/'+r+'.mp4'
                            break;
                        default:
                            break;
                    }
                    await axios.post(process.env.APP_API+'evento', {
                        'clase': 'output',
                        'mensaje': msg.body,
                        'tipo': 'chat_multimedia',
                        'file': mifile,
                        'bot': req.body.codigo,
                        'whatsapp': msg.timestamp,
                        'extension': media.mimetype,
                        'desde': req.body.codigo,
                        'author': req.body.codigo
                    })
                }else{
                    await axios.post(process.env.APP_API+'evento', {
                        'clase': 'output',
                        'mensaje': msg.body,
                        'tipo': 'chat_private',
                        'bot': req.body.codigo,
                        'whatsapp': msg.timestamp,
                        'desde': req.body.codigo,
                        'author': req.body.codigo,
                    })
                }

            }
        } catch (error) {
            console.log(error)        
        }
    });

    wbot.on('group_join', async (notification) => {
        // User has joined or been added to the group.
        console.log('join', notification);
        // notification.reply('User joined.');
        await axios.post(process.env.APP_API+'evento', {
            'clase': 'input',
            'tipo': 'join',
            'bot': req.body.codigo,
            'mensaje': notification.id.participant+' se unio a al grupo '+ notification.id.remote,
            'datos': notification,
            'desde': notification.chatId,
            'author': notification.id.participant,
            // 'grupo': notification.chatId
        })
    });
    
    wbot.on('group_leave', async (notification) => {
        // User has left or been kicked from the group.
        console.log('leave', notification);
        // notification.reply('User left.');
        await axios.post(process.env.APP_API+'evento', {
            'clase': 'input',
            'tipo': 'leave',
            'bot': req.body.codigo,
            'mensaje': notification.id.participant+' salio del grupo '+ notification.id.remote,
            'datos': notification,
            'desde': notification.chatId,
            'author': notification.id.participant,
            // 'grupo': notification.chatId
        })
    });
    
    wbot.on('vote_update', async (vote) => {
        /**
         * The {@link vote} that was affected:
         * 
         * {
         *   voter: 'number@c.us',
         *   selectedOptions: [ { name: 'B', localId: 1 } ],
         *   interractedAtTs: 1698195555555,
         *   parentMessage: {
         *     ...,
         *     pollName: 'PollName',
         *     pollOptions: [
         *       { name: 'A', localId: 0 },
         *       { name: 'B', localId: 1 }
         *     ],
         *     allowMultipleAnswers: true,
         *     messageSecret: [
         *        1, 2, 3, 0, 0, 0, 0, 0,
         *        0, 0, 0, 0, 0, 0, 0, 0,
         *        0, 0, 0, 0, 0, 0, 0, 0,
         *        0, 0, 0, 0, 0, 0, 0, 0
         *     ]
         *   }
         * }
         */
        await axios.post(process.env.APP_API+'evento', {
            'clase': 'input',
            // 'mensaje': req.body.title,
            'tipo': 'poll',
            'bot': req.body.codigo,
            'desde': vote.voter,
            'datos': vote
        })
        var midata = await axios.post(process.env.APP_API+'encuesta', {
            'interractedAtTs': vote.interractedAtTs,
            'selectedOptions': vote.selectedOptions,
            'isSentCagPollCreation': vote.parentMessage.isSentCagPollCreation,
            'messageSecret': vote.parentMessage.messageSecret,
            'pollInvalidated': vote.parentMessage.pollInvalidated,
            'pollOptions': vote.parentMessage.pollOptions,
            'voter': vote.voter,
            'pollName': vote.parentMessage.pollName,
            'allowMultipleAnswers': vote.parentMessage.allowMultipleAnswers
            // 'parentMessage': vote.parentMessage
        })
        console.log(vote);
    });

    await axios.post(process.env.APP_API+'evento', {
        'clase': 'input',
        'tipo': 'init',
        'bot': req.body.codigo,
        'mensaje': 'Iniciando el BOT: '+req.body.nombre+", espere un monento..",
    })

    wbot.initialize();
    res.send(true)
});

app.post('/stop', async (req, res) => {
    console.log(req.query)
    try {        
        fs.rmSync('.wwebjs_auth/session-'+req.query.nombre, { recursive: true, force: true }); 
        var miwbot = sessionstorage.getItem(req.query.nombre)
        sessionstorage.removeItem(req.query.nombre)
        miwbot.destroy()
        await axios.post(process.env.APP_API+'estado', {
            'bot': req.query.codigo,
            'estado': false
        })
        await axios.post(process.env.APP_API+'evento', {
            'clase': 'input',
            'tipo': 'destroy',
            'bot': req.query.codigo,
            'mensaje': 'El bot '+req.query.nombre+', fue eliminado'
        })
       
        console.log('El bot '+req.query.nombre+', fue eliminado')
    } catch (error) {
        console.log(error)        
    }
    res.send(true)
});

app.post('/getContactById', async (req, res) => {
    console.log(req.query)
    try {    
        var miwbot = sessionstorage.getItem(req.query.nombre)
        const ch = await miwbot.getChatById(req.query.contacto);
        console.log("chat here", ch);
        res.send(true)
    } catch (error) {
            console.log(error)
    }
    res.send(true)
});

app.post('/contactos', async (req, res) => {
    console.log(req.body)
    
    try {            
        var miwbot = sessionstorage.getItem(req.body.nombre)
        const contacts = await miwbot.getContacts();
        for (let index = 0; index < contacts.length; index++) {                                 
            if (contacts[index].isMyContact) {                                         
                await axios.post(process.env.APP_API+'contactos', {
                    'midata': contacts[index],
                    'bot': req.body.codigo,
                    '_id': contacts[index].id,
                    'number': contacts[index].number,
                    'avatar': null,
                    'tipo': 'contactos',
                    'user_id': req.body.user_id
                })   
                console.log('Contacto: '+contacts[index].number+' agregado..!');
                const url = await contacts[index].getProfilePicUrl();
                if (url) {
                    const response = await axios.get(url, { responseType: 'arraybuffer' })
                    let r = (Math.random() + 1).toString(36).substring(3)
                    var mifile = '../storage/'+req.body.user+'/'+r+'.jpeg'

                    fs.writeFile(mifile, response.data, (err) => {
                        if (err) throw err;
                        console.log('Image downloaded successfully!');
                    });
                    await axios.post(process.env.APP_API+'contactos', {
                        'midata': contacts[index],
                        'bot': req.body.codigo,
                        '_id': contacts[index].id,
                        'number': contacts[index].number,
                        'avatar': req.body.user+'/'+r+'.jpeg',
                        'tipo': 'contactos',
                        'codigo': contacts[index].id._serialized,
                        'user_id': req.body.user_id
                    })   
                }          
            }
        }
    } catch (error) {
        console.log(error)
    }
    res.send(true)
});

app.post('/historial', async (req, res) => {
    console.log(req.query)    
    try {    
        var miwbot = sessionstorage.getItem(req.query.nombre)
        const historial = await miwbot.getChats();        
        for (let index = 0; index < historial.length; index++) {             
            if (historial[index].isGroup) {
                // console.log(historial[index])
                var midata = await axios.post(process.env.APP_API+'grupos', {
                    // 'midata': historial[index],
                    'name': historial[index].name,
                    'bot': req.query.codigo,
                    'codigo': historial[index].groupMetadata.id._serialized,
                    '_id': historial[index].id,
                    'groupMetadata': historial[index].groupMetadata,
                    'lastMessage': historial[index].lastMessage,
                    'isReadOnly': historial[index].isReadOnly,
                    'isMuted': historial[index].isMuted ? true : false,
                    'tipo': 'grupos',
                    'owner': historial[index].groupMetadata.owner,
                    'desc': historial[index].groupMetadata.desc,
                    'creation': historial[index].groupMetadata.creation,
                    'user_id': req.query.user_id
                })   
                console.log(midata.data)
            }
        }
        // console.log(midata.data)
    } catch (error) {
        console.log(error)
    }
    res.send(true)
});

app.post('/send', async (req, res)=>{
    console.log(req.body)

    try {
        var miwbot = sessionstorage.getItem(req.body.codigo)
        // var phone = req.body.phone
        // var message = req.body.message
        // miwbot.sendMessage(phone, message).then(() => {
        //     console.log("si se envio el chat")
        // }).catch(() => {
        //     micount++
        //     console.log("no se envio el chat")
        // })      

        var midata = await miwbot.sendMessage(req.body.phone, new Poll(req.body.title, ['SI', 'NO'], { allowMultipleAnswers: false })).then(() => {
            console.log("encuesta SI enviada")
        }).catch(() => {
            console.log("encuesta NO enviada")
        })
        console.log(midata)
        // if (micount > 2) {
        //     micount = 0
        //     fs.rmSync('.wwebjs_auth/session-'+req.body.bot, { recursive: true, force: true }); 
        //     await axios.post(process.env.APP_API+'estado', {
        //         'bot': req.query.bot,
        //         'estado': false
        //     })
        // }

        // let sections = [
        //     { title: 'sectionTitle', rows: [{ title: req.body.title, description: 'desc' }, { title: 'ListItem2' }] }
        // ];
        // let list = new List('List body', 'btnText', sections, 'Title', 'footer');
        // miwbot.sendMessage(req.body.phone, list);
    } catch (error) {
        console.log(error)
    }             
    res.send(true)       
})

app.post('/template', async (req, res)=>{
    console.log(req.body)

    try {
        var miwbot = sessionstorage.getItem(req.body.bot)

        // stats = { size: 0 }
        if (miwbot) {    
            // grupos            
            for (let index = 0; index < req.body.grupos.length; index++) {
                if (req.body.multimedia) {                    
                    // stats = fs.statSync('../storage/'+req.body.multimedia); 
                    const media = MessageMedia.fromFilePath('../storage/'+req.body.multimedia)
                    await miwbot.sendMessage(req.body.grupos[index], media, {caption: req.body.message}).then(() => {
           
                        console.log("mensaje enviado")
                    }).catch(() => {
            
                        console.log("no se envio el chat")
                    })
                }else{
                    await miwbot.sendMessage(req.body.grupos[index], req.body.message).then(() => {
                    
                        console.log("si se envio el chat")
                    }).catch(() => {
                   
                        console.log("no se envio el chat")
                    })
                }
            }

            //contactos
            for (let index = 0; index < req.body.contactos.length; index++) {
                if (req.body.multimedia) {   
                    const media = MessageMedia.fromFilePath('../storage/'+req.body.multimedia)
                    await miwbot.sendMessage(req.body.contactos[index], media, {caption: req.body.message}).then(() => {
             
                        console.log("mensaje enviado")
                    }).catch(() => {
              
                        console.log("no se envio el chat")
                    })
                }else{
                    await miwbot.sendMessage(req.body.contactos[index], req.body.message).then(() => {
                   
                        console.log("si se envio el chat")
                    }).catch(() => {
                  
                        console.log("no se envio el chat")
                    })
                }
            }

            //actualizar plantilla
            // await axios.post(process.env.APP_API+'template/update', {
            //     'id': req.body.id,
            //     'send': misend,
            //     'size': stats.size
            // })
 
            // if (micount >= 2) {
            //     console.log("eliminado .."+micount)
            //     micount = 0
            //     miwbot.destroy()
            //     fs.rmSync('.wwebjs_auth/session-'+req.body.bot, { recursive: true, force: true }); 
            //     await axios.post(process.env.APP_API+'estado', {
            //         'bot': req.body.codigo,
            //         'estado': false
            //     })
            // }
        }
    } catch (error) {
        console.log(error)
    }             
    res.send(true)        
})

//------------YT-DLP-----------------
app.post('/download', async (req, res) => {
    console.log(req.body)

    try {        
        if (!fs.existsSync('../storage/'+req.body.name)){
            fs.mkdirSync('../storage/'+req.body.name);
        }

        let stdout = await ytDlpWrap.execPromise([
            req.body.url,
            '-f',
            'best',
            '-o',
            '../storage/'+req.body.name+'/'+req.body.slug+'.mp4'
        ]);      
        
        console.log(stdout);
        var mifile = req.body.name+'/'+req.body.slug+'.mp4'
        //agregar evento
        await axios.post(process.env.APP_API+'evento', {
            'clase': 'input',
            'tipo': 'chat_multimedia',
            'extension': 'video/mp4',
            'bot': req.body.codigo,
            'file': mifile,
        })

        //actualizar descarga
        var stats = fs.statSync('../storage/'+req.body.name+'/'+req.body.slug+'.mp4');  
        await axios.post(process.env.APP_API+'download/update', {
            'slug': req.body.slug,
            'file': mifile,
            'size': stats.size
        })

    } catch (error) {
        console.log(error)
    }
    res.send(true)
});


//------- AI API ---------------------
app.post('/ai/run', async (req, res)=>{
    console.log(req.body)

    switch (req.body.type) {
        case 'text':
            const input = {
                debug: false,
                top_k: 50,
                top_p: 1,
                prompt: "¿Puedes escribir un poema sobre el aprendizaje automático de código abierto?",
                temperature: 0.5,
                system_prompt: "Eres un asistente servicial, respetuoso y honesto. Responda siempre de la manera más útil posible y siendo seguro. Tus respuestas no deben incluir ningún contenido dañino, poco ético, racista, sexista, tóxico, peligroso o ilegal. Asegúrese de que sus respuestas sean socialmente imparciales y de naturaleza positiva.\n\nSi una pregunta no tiene ningún sentido o no es objetivamente coherente, explique por qué en lugar de responder algo que no sea correcto. Si no sabe la respuesta a una pregunta, no comparta información falsa, y solo responderas en español latino.",
                max_new_tokens: 500,
                min_new_tokens: -1
              };
              
              var mitext = ''
              for await (const event of replicate.stream("meta/llama-2-70b-chat", { input })) {
                process.stdout.write(event.toString());
                // console.log(event.toString())
                mitext = mitext + event.toString()
              };
              console.log('-----------RESPUESTA-------------\n')
              console.log(mitext)
            break;
        case 'images':
            const media = await MessageMedia.fromUrl(output[0])
            const imgBuffer = Buffer.from(media.data, 'base64');
            let r = (Math.random() + 1).toString(36).substring(3);
            fs.writeFileSync('../storage/'+r+'.png', imgBuffer);
            break;
        default:
            break;
    }
    const output = await replicate.run(
        "stability-ai/sdxl:610dddf033f10431b1b55f24510b6009fcba23017ee551a1b9afbc4eec79e29c",
        {
          input: {
            width: 512,
            height: 512,
            prompt: "A studio photo of a rainbow coloured dog",
            refine: "expert_ensemble_refiner",
            scheduler: "KarrasDPM",
            num_outputs: 1,
            guidance_scale: 7.5,
            high_noise_frac: 0.8,
            prompt_strength: 0.8,
            num_inference_steps: 50
          }
        }
      );



    
      res.send(true)
})
