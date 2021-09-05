<?php

error_reporting(0);

include_once "paths.php";

    if ( empty( $_REQUEST["cmd"] ) ) {



    } else {

        switch ( $_REQUEST["cmd"] ) {
            case "getlistconnectplayers": getlistconnectplayers(); break;
            case "getlistservers":        getlistservers(); break;
            case "createserver":          createserver(); break;
            case "closeserver":           closeserver(); break;
            case "checkcheckserver":      checkcheckserver(); break;
            case "gatsetinfoplayer":      gatsetinfoplayer(); break;
        }

        exit;

    }

function gatsetinfoplayer() {

    $path = CL_Path_Lib::$save_dir . CL_Path_Lib::$info_dir . $_REQUEST["param1"] . ".ini";

    if ( file_exists( $path ) ) {

        echo ( file_get_contents( $path ) );

    } else
        echo "Нет Информации о игроке";

    exit;

}
function checkcheckserver() {

    if ( !preg_match( "/([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3})\:([0-9]{1,5})/si" , $_REQUEST["param1"] , $res ) )
        exit;

    if ( !( $fsock = @fsockopen("udp://".$res[1], $res[2], $errnum, $errstr,2) ) )
        exit;

    for ( $i = 1; $i <= 10; $i++ ) {

        @fwrite( $fsock, "\xFF\xFF\xFF\xFFchecksvc" );

        sleep( 1 );

    }

    fclose($fsock);

    exit;

}
function createserver() {

    $fp = fsockopen( CL_Path_Lib::$DOMEN , 80 );
    if ( $fp ) {
        fputs($fp, "GET /engine.svc.php?key=".CL_Path_Lib::$keyStart."&create_svc_port=".$_REQUEST["param1"] ." HTTP/1.0\nUser-Agent: У меня Firefox 1.5 и Windows XP\nReferer: Я пришёл с microsoft.com\nHOST: ".CL_Path_Lib::$DOMEN."\n\n");
        fclose($fp);
    }
}
function closeserver() {

    if ( preg_match( "/[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\:[0-9]{1,5}/si" , $_REQUEST["param1"]  ) === 0 )
        exit;

    $path = preg_replace( "/:/si" , "." , $_REQUEST["param1"] );
    $path1 = preg_replace( "/:/si" , "." , $_REQUEST["param1"] );

    if ( !unlink( CL_Path_Lib::$job_file_dir . $path ) )
        rename ( CL_Path_Lib::$job_file_dir . $path , CL_Path_Lib::$job_file_dir . md5( rand(1,1000000)."_".rand(1,1000000) ) );

    if ( !unlink( CL_Path_Lib::$trade_info . $path ) )
        rename ( CL_Path_Lib::$trade_info . $path , CL_Path_Lib::$trade_info . md5( rand(1,1000000)."_".rand(1,1000000) ) );

    $fp = fsockopen( CL_Path_Lib::$DOMEN , 80 );
    if ( $fp ) {
        fputs($fp, "GET /?cmd=checkcheckserver&param1=".$_REQUEST["param1"] ." HTTP/1.0\nUser-Agent: У меня Firefox 1.5 и Windows XP\nReferer: Я пришёл с microsoft.com\nHOST: ".CL_Path_Lib::$DOMEN."\n\n");
        fclose($fp);
    }

  //  sleep( 2 );

    echo "true";

    exit;

}
function getlistservers() {

    $result = Array();

    if ( !( $dir = opendir( CL_Path_Lib::$job_file_dir ) ) )
        exit;

    while ( !( ( $file = readdir( $dir ) ) === false ) )
        if ( !( ( $file === "." ) || ( $file === ".." ) ) ) {
            $len = strlen( $file )-1;
            for ( $i=$len; $i>=0; $i-- )
                if ( $file[$i] === "." ) {
                    $file[$i] = ":";
                    break;
                }

            $result[] = $file;

        }

    closedir( $dir );

    echo json_encode( $result );

    exit;

}
function getlistconnectplayers() {

    $addr = empty( $_REQUEST["param1"] ) ? false : preg_replace( "/\:/si" , "." , $_REQUEST["param1"] );

    if ( $addr === false )
        exit;

    $numLine = empty( $_REQUEST["param2"] ) ? 0 : $_REQUEST["param2"];

    $res = file( CL_Path_Lib::$trade_info . $addr , FILE_IGNORE_NEW_LINES );

    if ( $numLine > count( $res )-1 )
        exit;

    echo $res[ $numLine ];

    exit;

}

?>


<html>
<head>
    <script src="http://code.jquery.com/jquery-1.11.0.min.js" type="text/javascript" ></script>
</head>

<body>

<style type="text/css" >
    .server_addr {
        padding: 5px;
    }
    .players_table tr {
        width: 100%;
    }
    .time_update {
        width: 300px;
        padding: 5px;
    }
    .name_player {
        width: 300px;
        padding: 5px;
    }
    .addr_player {
        padding-left: 10px;
        padding-right: 30px;
    }
    .hover_players_item {
        cursor: pointer;
    }
</style>

<div><input type="text" id="my_port_id" value="27015" ><button id="create_svc">Создать сервер</button></div>

<div id="status_server_info" style="height: 200px;border: 2px solid #424242; float: left; width: 100%; overflow-y: scroll; overflow-x: hidden;" ></div>

<table id="server_table" border="1px" style="width: 100%;" >
    <tr id="is_next_append_servers" >
        <td>Сервера:</td>
    </tr>
</table>

<div style="width: 100%; height: 100%; background: #2a1f79; position: fixed; opacity: 0.90; z-index: 12;top:0px;left:0px; display: none;" id="fon_global" ></div>
<div style="width: 800px; height: 600px; background: #424242; position: fixed; border-radius: 10px; z-index: 99; display: none;" id="showPlayersInfo" >
    <div style="background: #FFF; margin: 10px; padding: 15px; border-radius: 10px; height: 550px; overflow-y: scroll;"><pre></pre></div>
</div>

<script type="text/javascript">

var prefix_mai = "mai_";
var prefix_svc = "svc_";
var prefix_cls = "cls_";
var prefix_pla = "pla_";
var prefix_one = "one_";
var prefix_two = "_tw_";

var time_update_server_list  = 1000;
var time_update_players_list = 1000;
var time_update_time_list    = 1000;

var key_create_server = "<?php echo CL_Path_Lib::$keyStart; ?>";

var GLOBAL_SVC_LIST = [];



(function( $ ){
    $( document).ready(function() {

        function getTimeStamp() {

            return Math.round ( (new Date().getTime()) / 1000 );

        }
        function getRealStringTimeDip( time1 , time2 ) {

            var rtime = time2 - time1;

            var chas = Math.floor( rtime / 3600 );
            var min  = Math.floor( rtime / 60   ) - chas * 60;
            var sec  = rtime - ( ( min * 60 ) + ( chas * 3600 ) );

            var chasStr = ( chas == 0 ) ? "" : chas+" час. ";
            var minStr  = ( min  == 0 ) ? "" : min +" мин. ";
            var secStr  = ( sec  == 0 ) ? "" : sec +" сек. ";

            return chasStr + minStr + secStr + "назад";

        }
        function addServer( addr , idAddr ) {

            var html = '<tr id="'+prefix_mai+idAddr+'" ><td><table border="1px" style="width: 100%;" >'+
                '<tr><td class="server_addr" style="width: 150px; text-align: center;">'+addr+'<br><button class="button_close_server" id="'+prefix_cls+idAddr+'" >Закрыть</button></td>'+
                '<td id="'+prefix_svc+idAddr+'" >' +
                '<table border="1px" style="width: 100%;" class="players_table" >'+
                '<tr id="'+prefix_pla+idAddr+'" >' +
                '<td class="">Время:</td><td>Имя:</td><td>ADDR:</td><td>Стеам:</td>'+
                '</tr>'+
                '</table>'+
                '</td>'+
                '</tr></table></td></tr>';

            var gl_len = GLOBAL_SVC_LIST.length;
            GLOBAL_SVC_LIST[GLOBAL_SVC_LIST.length] = {
                elem: $('#is_next_append_servers').after( html ),
                addr: addr,
                id: idAddr,
                playerCurrAddId: 0
            };


            $('#'+prefix_cls+idAddr).on('click.close_server' , function(e) {
                $.post( "?cmd=closeserver&param1="+addr , function( data ) {
                    if ( data == "true" ) {

                        GLOBAL_SVC_LIST.splice( gl_len , 1 );

                        $('#'+prefix_mai+idAddr).remove();

                    }
                });
            });

        }
        function checkServers() {

            $.post( "?cmd=getlistservers" , function( data ) {

                data = JSON.parse( data );

                for ( var i = 0; i < data.length; i++ ) {

                    var idAddr = data[i].replace( "." , "").replace( "." , "").replace( "." , "").replace( "." , "").replace( ":" , "" );

                    if ( $( '#'+prefix_svc+idAddr ).length === 0 )
                        addServer( data[i] , idAddr );

                }

            });

        }
        function chechPlayersConnectInfo( id ) {

            $.post( "?cmd=getlistconnectplayers&param1="+GLOBAL_SVC_LIST[ id ].addr+"&param2="+GLOBAL_SVC_LIST[ id ].playerCurrAddId , function( data ) {

                if ( data.length > 10 ) {

                    data = JSON.parse( data );

                    var html = '<tr id="'+prefix_one+ GLOBAL_SVC_LIST[ id ].id +prefix_two+GLOBAL_SVC_LIST[ id ].playerCurrAddId+'" class="hover_players_item" >' +
                        '<td class="time_update">'+getRealStringTimeDip(data.time , getTimeStamp())+'</td><td class="name_player">'+data.name+'</td><td class="addr_player">'+data.addr+'</td><td class="steam_player">'+data.steam+'</td>'+
                        '</tr>';

                    $( '#'+prefix_pla+ GLOBAL_SVC_LIST[ id ].id ).after( html );

                    $( '#'+prefix_one+ GLOBAL_SVC_LIST[ id ].id +prefix_two+GLOBAL_SVC_LIST[ id ].playerCurrAddId+' .time_update').data( "time" , data.time );

                    var trade_path = data.path;

                    $('#'+prefix_one+ GLOBAL_SVC_LIST[ id ].id +prefix_two+GLOBAL_SVC_LIST[ id ].playerCurrAddId).hover(
                        function() {
                            $(this).css('background' , '#424242');
                            $(this).css('color' , '#30FF30');
                        } ,
                        function() {
                            $(this).css('background' , '#FFF');
                            $(this).css('color' , '#000');
                        }
                    ).click(function() {

                            $.post("?cmd=gatsetinfoplayer&param1=" + trade_path , function( info ) {
                                $('#fon_global').css("display" , "block");
                                $('#showPlayersInfo').css("display" , "block").find("div").find("pre").text( info );
                            });

                    });

                    GLOBAL_SVC_LIST[ id ].playerCurrAddId++;

                }

            });


        }
        function checkPlayersInServers() {

            GLOBAL_SVC_LIST.forEach(function( val , i ){
                chechPlayersConnectInfo( i );
            });

        }

        var checkServerInterval = setInterval( checkServers , time_update_server_list );
        var checkPlayersInterval = setInterval( checkPlayersInServers , time_update_players_list );
        var realTimeInterval = setInterval( function() {

            var time = getTimeStamp();

            $(".time_update").each(function() {

                $(this).text( getRealStringTimeDip( $(this).data( "time" ) , time ) );

            });

        } , time_update_time_list );

        $('#create_svc').on('click.create_svc' , function(e) {

            $.post( "engine_svc.php?key="+key_create_server+"&create_svc_port="+$("#my_port_id").val() , function( data ) {

                $("#status_server_info").prepend( "<div style='padding-left: 20px; padding-right: 20px; border: 2px solid #423654; margin: 8px;'>"+data+"</div>" );

            });

        });

        function setWind() {

            var wind = $( document );
            var elem = $('#showPlayersInfo');
            var x , y;

            x = Math.round( ( wind.width()  - elem.width()  ) / 2);
            y = Math.round( ( wind.height() - elem.height() ) / 2 );

            elem.css("left",x+"px").css("top",y+"px");

        }
        $( document).resize( setWind );
        setWind();

        $("#fon_global").click(function(){

            $('#fon_global').css("display" , "none");
            $('#showPlayersInfo').css("display" , "none");

        });

    });
})(jQuery);



</script>
</body>

</html>