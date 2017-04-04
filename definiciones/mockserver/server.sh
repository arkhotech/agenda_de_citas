#!/bin/bash
#
# Designed for running SoapUI mockservicerunner.sh script in background as command line tool
# Credits to: tinogomes on github https://gist.github.com/tinogomes/447191
# References:
# http://stackoverflow.com/questions/14061876/control-to-the-next-statement-after-running-eval-command
# http://stackoverflow.com/questions/392022/best-way-to-kill-all-child-processes/6481337#6481337

# Variables to edit according to your environment
SOAPUI_HOME=/opt/SoapUI-5.2.1
USR=`echo whoami`
# Default variables
PID=soapui-server.pid
LOG=soapui-server.log
#Project name containing spaces goes in ""
COMMAND="$SOAPUI_HOME/bin/mockservicerunner.sh -p 9098 -m \"Calendar REST Services\" agendas-mockups-SOAPUI.xml"
#To be used to kill child process
PID_KEYWORD='soapui'
echo "Running "$COMMAND
 
status() {
    if [ -f $PID ]
    then
        echo
        echo "Pid file: $( cat $PID ) [$PID]"
        echo
        ps -ef | grep -v grep | grep $( cat $PID )
    else
        echo
        echo "No Pid file"
    fi
}

start() {
  if [ -f $PID ]
    then
        echo
        echo "Already started. PID: [$( cat $PID )]"
    else
        touch $PID
        #eval command for avoid conflicts with "" in the command line parameters
        if (nohup `eval  $COMMAND`) >>$LOG 2>&1 &
        then echo $! >$PID
             echo "Done."
             echo "$(date '+%Y-%m-%d %X'): START" >>$LOG
        else echo "Error... "
             /bin/rm $PID
        fi
    fi
}

stop() {
    if [ -f $PID ]
    then
        if kill $( cat $PID )
        then echo "Done."
             echo "$(date '+%Y-%m-%d %X'): STOP" >>$LOG
        fi
        /bin/rm $PID
        kill_cmd
    else
        echo "No pid file. Already stopped?"
    fi
}

kill_cmd() {
    SIGNAL=""; MSG="Killing "
    while true
    do
        LIST=`ps -ef | grep -v grep | grep $PID_KEYWORD | grep -w $USR | awk '{print $2}'`
        if [ "$LIST" ]
        then
            echo; echo "$MSG $LIST" ; echo
            echo $LIST | xargs kill $SIGNAL
            sleep 2
            SIGNAL="-9" ; MSG="Killing $SIGNAL"
            if [ -f $PID ]
            then
                /bin/rm $PID
            fi
        else
           echo; echo "All killed..." ; echo
           break
        fi
    done
}

case "$1" in
    'start')
            start
            ;;
    'stop')
            stop
            ;;
    'restart')
            stop ; echo "Sleeping..."; sleep 1 ;
            start
            ;;
    'status')
            status
            ;;
    *)
            echo 
            echo "Usage: $0 { start | stop | restart | status }"
            echo
            exit 1
            ;;
esac

exit 0
