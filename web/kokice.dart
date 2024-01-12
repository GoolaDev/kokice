import 'dart:html';
import 'dart:math';
import 'package:http/http.dart' as http;

int numberOfMediaFiles = 0;
int activeTeam = 0;
const String mediaListFileName = "_medialist.txt";
const String hostAndPort = "http://127.0.0.1:8080";
List<String> mediaFilesList = [];
String? testStr ="";
void main() async
{
    try 
    {
        // table stuff
        querySelectorAll(".tdscore").forEach((element) => element.innerText="0");
        querySelectorAll(".tdscore").forEach((element) => element.onClick.listen((MouseEvent event) 
        { 
           String id = (event.target as TableCellElement).id;
           id = "#$id";
           int score = int.parse( querySelector(id)!.innerText );
           //window.alert(score.toString());
           if (event.ctrlKey) --score; 
           if (event.altKey) ++score;
           querySelector(id)!.innerText = score.toString();
        }));
        
        final Uri mediaListUri = Uri.parse('$hostAndPort/$mediaListFileName');
        final String mediaListFile = await http.read(mediaListUri);
        
        if (mediaListFile.isEmpty) { throw "Content: 0"; }
        mediaFilesList = mediaListFile.split('\r\n') ;
        if (mediaFilesList.last.isEmpty) mediaFilesList.removeLast();
        numberOfMediaFiles = mediaFilesList.length;
        mediaFilesList.shuffle(Random());
        mediaFilesList.shuffle(Random());
        mediaFilesList.shuffle(Random());
        switchTeam();
        createButtons();
    }

    catch (error)
    {
        window.alert("NESHTO SHTEKA.\n${error.toString()}");   
    }
}

void createButtons()
{
    for (int btnNo = 1; btnNo <= numberOfMediaFiles; ++btnNo)
    {
        ButtonElement btnElement = ButtonElement();
        querySelector("#buttons_container")!.append(btnElement);
        btnElement.id = btnNo.toString();
        btnElement.type = "button";
        btnElement.text = btnNo.toString();
        btnElement.classes.addAll(["button","button_on"]);
        btnElement.onClick.listen(buttonsOnClickHandler);        
    }
}

void switchTeam()
{   
    if ( ((querySelector('#teamnameinput1') as InputElement).value!.isEmpty) &&
         ((querySelector('#teamnameinput2') as InputElement).value!.isEmpty) &&
         ((querySelector('#teamnameinput3') as InputElement).value!.isEmpty) &&
         ((querySelector('#teamnameinput4') as InputElement).value!.isEmpty) 
    ) return; 
    
    const String activeColor = "green";
    const String inactiveColor = "red";
    activeTeam = activeTeam % 4+1;
    
    String thId="#teamname${activeTeam.toString()}";
    String inputId="#teamnameinput${activeTeam.toString()}";
    
    querySelectorAll('.teamname').style.backgroundColor = inactiveColor;
    querySelector(thId)!.style.backgroundColor = activeColor;
    querySelector(inputId)!.style.backgroundColor = activeColor;
    //document.title = (querySelector(inputId) as InputElement).value!.isEmpty.toString() ;
    if ( (querySelector(inputId) as InputElement).value!.isEmpty ) switchTeam(); 
}

void buttonsOnClickHandler(MouseEvent event) async
{
    ButtonElement btnElement = event.target as ButtonElement;
    try 
    { 
        final String mediaName = mediaFilesList[int.tryParse(btnElement.id)!-1];
        final Uri mediaListUri = Uri.parse('$hostAndPort/player.php?medianame=$mediaName');
        final serverResponse = await http.get(mediaListUri);
        
        if (serverResponse.statusCode != 200) 
        {
            throw "Stutus code: ${serverResponse.statusCode}";
        }
        else if (!serverResponse.body.contains('STATUS=OK'))
        {
            throw "Server Response: ${serverResponse.body}";
        }
        
        btnElement.classes.remove("button_on");
        btnElement.classes.add("button_off");
        switchTeam();

    }
    catch (error)
    {
        window.alert("NESHTO SHTEKA.\n${error.toString()}");
    }   
}
