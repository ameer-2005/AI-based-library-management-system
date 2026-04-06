function searchBooks(){

let input=document.getElementById("search").value;

fetch("search_api.php?q="+input)
.then(res=>res.text())
.then(data=>{
document.getElementById("results").innerHTML=data;
});

}