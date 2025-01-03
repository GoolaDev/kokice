import 'dart:html';
import 'dart:math';
import 'package:html/parser.dart' as html;
import 'package:http/http.dart' as http;

int numberOfMediaFiles = 0;
int activeTeam = 0;
const String mediaListCmd = "command=listMedia";
const String playMediaCmd = "command=playMedia&medianame=";
const String backendFileName = "player.php?";
final String? docUri = window.document.baseUri;
String docUrl = "";
String partialCommandUri = "";
List<String> mediaFilesList = [];
String? testStr ="";

void logToWeb([String? msg=''])
{
    window.alert(msg);
}

void main() async
{
    try 
    {   // Get URL
        if (docUri!=null)
        {
            docUrl = docUri!.substring(0,docUri!.indexOf('kokice')); 
        }
        else
        {
            logToWeb('Get document url failed!');
            return;
        }
        partialCommandUri = docUrl + backendFileName;
        
        querySelectorAll(".tdscore").forEach((element) => element.innerText="0");
        querySelectorAll(".tdscore").forEach((element) => element.onClick.listen((MouseEvent event) 
        { 
           String id = (event.target as TableCellElement).id;
           id = "#$id";
           int score = int.parse( querySelector(id)!.innerText );
           if (event.ctrlKey) --score; 
           if (event.altKey) ++score;
           querySelector(id)!.innerText = score.toString();
        }));
        
        final Uri mediaListUri = Uri.parse('$partialCommandUri$mediaListCmd');
        final String mediaListFileDocument = await http.read(mediaListUri);
        if (mediaListFileDocument.isEmpty) { throw "Content: 0"; }
        var mediaListDocument = html.parse(mediaListFileDocument);
        String? mediaListContainer = mediaListDocument.body?.querySelector('#MediaListContainer')?.innerHtml;
        
        if (mediaListContainer == null)   { throw "Could not get media list. Probably web server issue.";  }
        if (mediaListContainer.isEmpty) { throw "Media List empty."; }
        
        mediaFilesList = mediaListContainer.split('<br>') ;
        if (mediaFilesList.last.isEmpty) mediaFilesList.removeLast();
        numberOfMediaFiles = mediaFilesList.length;

        mediaFilesList.shuffle(Random());
        mediaFilesList.shuffle(Random());
        mediaFilesList.shuffle(Random());

        switchTeam();
        createButtons();
        registerTeamNameInputsClickHandlers();
    }

    catch (error)
    {
        window.alert("Something's Wrong.\n${error.toString()}");   
    }
}

void registerTeamNameInputsClickHandlers()
{
    querySelector("#teamnameinput1")!.onClick.listen((MouseEvent event) { if (event.shiftKey) {activeTeam = 4; switchTeam();} });
    querySelector("#teamnameinput2")!.onClick.listen((MouseEvent event) { if (event.shiftKey) {activeTeam = 1; switchTeam();} });            
    querySelector("#teamnameinput3")!.onClick.listen((MouseEvent event) { if (event.shiftKey) {activeTeam = 2; switchTeam();} });
    querySelector("#teamnameinput4")!.onClick.listen((MouseEvent event) { if (event.shiftKey) {activeTeam = 3; switchTeam();} });      
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

void switchTeam([bool forward = true])
{   
    if ( ((querySelector('#teamnameinput1') as InputElement).value!.isEmpty) &&
         ((querySelector('#teamnameinput2') as InputElement).value!.isEmpty) &&
         ((querySelector('#teamnameinput3') as InputElement).value!.isEmpty) &&
         ((querySelector('#teamnameinput4') as InputElement).value!.isEmpty) 
    ) return; 

    const String activeColor = "green";
    const String inactiveColor = "red";
    
    if (forward) 
    {
        activeTeam = activeTeam % 4+1;
    }    
    else
    {
        activeTeam--;
        if (activeTeam < 1) activeTeam = 4;    
    }
    
    
    String thId="#teamname${activeTeam.toString()}";
    String inputId="#teamnameinput${activeTeam.toString()}";
       
    querySelectorAll('.teamname').style.backgroundColor = inactiveColor;
    querySelector(thId)!.style.backgroundColor = activeColor;
    querySelector(inputId)!.style.backgroundColor = activeColor;
    
    // if Team name is missing switch over it
    if ( (querySelector(inputId) as InputElement).value!.isEmpty ) switchTeam(); 
}

void buttonsOnClickHandler(MouseEvent event) async
{
    ButtonElement btnElement = event.target as ButtonElement;
    
    try 
    { 
        if (event.ctrlKey) // undoes clicked button 
        {
            btnElement.classes.remove("button_off");
            btnElement.classes.add("button_on");
            switchTeam(false);
            return;   
        }
    
    
        final String mediaName = mediaFilesList[int.tryParse(btnElement.id)!-1];
        final Uri mediaListUri = Uri.parse('$partialCommandUri$playMediaCmd$mediaName');
        
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
        
        switchTeam(true);

    }
    catch (error)
    {
        window.alert("AGGRRRrrr!!!.\n${error.toString()}");
    }   
}
