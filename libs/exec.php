<?php

/**
 * @author     Ashraf M Kaabi
 * @name      : Advance Linux Exec
 */
class exec {
	/**
	 * Run Application in background
	 *
	 * @param     string $Command
	 * @param     int $Priority
	 *
	 * @return    int PID
	 */
	public static function background( $Command, $Priority = 0 ) {
		if( $Priority ) {
			$PID = shell_exec( "nohup nice -n $Priority $Command > /dev/null & echo $!" );
		} else {
			$PID = shell_exec( "nohup $Command > /dev/null & echo $!" );
		}
		return ( $PID );
	}

	/**
	 * Check if the Application running !
	 *
	 * @param     int $PID
	 *
	 * @return     boolean
	 */
	public static function is_running( $PID ) {
		exec( "ps $PID", $ProcessState );
		return ( count( $ProcessState ) >= 2 );
	}

	/**
	 * Kill Application PID
	 *
	 * @param  int $PID
	 *
	 * @return boolean
	 */
	public static function kill( $PID ) {
		if( exec::is_running( $PID ) ) {
			exec( "kill -KILL $PID" );
			return true;
		} else {
			return false;
		}
	}
}