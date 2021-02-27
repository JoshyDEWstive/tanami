import requests


while True:
        url = 'http://localhost/actions.php';




        command = input("Command: ");

        if(command == 'exit'):
                break;

        myobj = {'action':command,'return':True};
        
        if(command == "add-seed"):
            print("Type the website you want to seed");
            myobj['data'] = input("URL: ");
        if(command == "search"):
            print("Keywords to search");
            myobj['data'] = input("Keywords: ");    
        x = requests.post(url, data = myobj);

        print(" == ");
        print(x);
        print(" == ");
        print(x.text);


