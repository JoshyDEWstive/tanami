
import requests

def GetRecordCount():
	url = 'http://localhost:666/tanami/actions.php'
	myobj = {'action':'record-count','return':True}

	x = requests.post(url, data = myobj)
	return x
	
while True:
	url = 'http://localhost:666/tanami/actions.php'
	myobj = {'action':'next-queue','return':True}

	x = requests.post(url, data = myobj)

	co = GetRecordCount()
	if(co.text == "50"):
		exit();
	
