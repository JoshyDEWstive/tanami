
import requests
import time

def GetRecordCount():
	url = 'http://localhost/actions.php'
	myobj = {'action':'record-count','return':True}

	x = requests.post(url, data = myobj)
	return x
	

goto = int(GetRecordCount().text) + 30;
goto = str(goto);
goto = "175";
previous = -1

while True:

	co = GetRecordCount()
    
	

	if(previous != co.text):
		previous = co.text
		url = 'http://localhost/actions.php'
		myobj = {'action':'next-queue','return':True}

		x = requests.post(url, data = myobj)
		print(co.text + " / "+ goto);
	else:
		print(".",end='');
    
	if(co.text == goto):
		exit();

	#time.sleep(2)
