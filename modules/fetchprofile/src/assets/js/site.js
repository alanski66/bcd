console.log('module js');



const $verifyButton = document.getElementById('verify');
const $btnTxt = document.getElementById('btnText');

const memberOrg = document.getElementById('memberOrg');
const idRouteContainer = document.getElementById('id-route-container');
const urlRouteContainerUKCP = document.getElementById('url-route-container-ukcp');
const urlRouteContainerOther = document.getElementById('url-route-container-other');
const vBtnContainer = document.getElementById('v-btn-container')
const vSuccessContainer = document.getElementById('verified-success')
const vWarningContainer = document.getElementById('verified-warning')

const isValidUrl = (urlString) => {
    var urlPattern = new RegExp('^(https?:\\/\\/)?' + // validate protocol
        '((([a-z\\d]([a-z\\d-]*[a-z\\d])*)\\.)+[a-z]{2,}|' + // validate domain name
        '((\\d{1,3}\\.){3}\\d{1,3}))' + // validate OR ip (v4) address
        '(\\:\\d+)?(\\/[-a-z\\d%_.~+]*)*' + // validate port and path
        '(\\?[;&a-z\\d%_.~+=-]*)?' + // validate query string
        '(\\#[-a-z\\d_]*)?$', 'i'); // validate fragment locator
    return !!urlPattern.test(urlString);
}

memberOrg.addEventListener('change', function (e) {
    //reset
    idRouteContainer.classList.add("d-none")
    urlRouteContainerUKCP.classList.add("d-none")
    urlRouteContainerOther.classList.add("d-none")

    if ((memberOrg.value === "bacp") || (memberOrg.value === "ncps")) idRouteContainer.classList.remove("d-none")
    if (memberOrg.value === "ukcp")  urlRouteContainerUKCP.classList.remove("d-none")
    if (memberOrg.value === "other")  urlRouteContainerOther.classList.remove("d-none")

});
$verifyButton.addEventListener('click', function (e) {
    const params = new FormData();
    params.append($verifyButton.dataset.csrfTokenName, $verifyButton.dataset.csrfTokenValue);
  
    if (memberOrg.value === "other") verifyother(params)
    if (memberOrg.value === "bacp") verifybacp(params) // format https://www.bacp.co.uk/therapists/386443
    if (memberOrg.value === "ukcp") verifyukcp(params) // eg https://www.psychotherapy.org.uk/therapist/Maja-Andersen-JJYWLQA5
    if (memberOrg.value === "ncps") verifyother(params) //format https://www.search-ncps.com/search/FindaTherapist/NCS14-01401
    vWarningContainer.classList.add("d-none")
    return

});

// el.addEventListener("click", function (e) {
//     e.preventDefault
//     console.log(this.className); // logs the className of my_element
//     console.log(e.currentTarget === this); // logs `true`
//     console.log(verifyLink.value); // logs `true`
//     if(!verifyLink.value){
//         console.log('error msg')
//     }else{
//         const data = JSON.stringify({link:verifyLink.value})
//         console.log(typeof data)
//         console.log(data)
//         verifybacp(data)
//     }
//   });

function verifyother(params) {
}
function verifybacp(params) {
    console.log('verify id membership')
    console.log(memberOrg.value)
    const profileIdEl = document.getElementById('v-url-route-id')
    let profileId = profileIdEl.value
    let errmsg = document.getElementById('err-routeid')
    let spinner = document.getElementById('spinner')

    
    spinner.classList.remove("d-none")
    errmsg.className = "fade-out-err"
    errmsg.innerText = "";
    setTimeout(() => {
        //handle invalid url errors
        if(profileId == null || profileId == "") {
            spinner.className = "d-none spinner-border spinner-border-sm"
            errmsg.className = "fade-in-err"
            errmsg.innerText = "Unable to verify - please add your membership ID. (If this keeps happening please contact us)";
            $verifyButton.className = "btn btn-warning"; 
        return
        }
    }, 3000);

    
    if (profileId.length > 0) {
        var preFixUrl = ""
        console.log("Match go verify ID");
        // bacp url https://www.bacp.co.uk/therapists/386443
        if (memberOrg.value == "bacp") {
            var preFixUrl = "https://www.bacp.co.uk/therapists/"  
        }

        let idLink = preFixUrl + profileId;
        console.log(idLink)
        params.append('verifyLink', preFixUrl + profileId);
        params.append('org', memberOrg.value);
        params.append('profileId', profileId);
        
        // console.log([...params]);
        
        fetch('/actions/fetch-profile/default/verify-bacp', {
            method: 'POST',
            body: params,
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        })
            .then(response => {
                // Check if the request was successful
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                // Parse the response as JSON
                return response.json();
            })
            .then(data => {
                // Handle the JSON data
                console.log('status:' + data.statuscode);
                if(data.statuscode == "true"){
                    
                    setTimeout(() => {
                        confetti.reset();
                           // confetti
                    confetti({
                        particleCount: 100,
                        spread: 70
                    });
                    // do button verify message
                    vSuccessContainer.classList.remove("d-none")
                    vBtnContainer.classList.add("d-none")
                    $verifyButton.className = "btn btn-success";
                    $verifyButton.innerText = "Verified!";
                    // $verifyButton.ariaDisabled;
                    // $verifyButton.disabled = true;
                      }, 3000);
                } else{
                    //do error msg
                    setTimeout(() => {
                    vWarningContainer.classList.remove("d-none")
                    spinner.classList.add("d-none");
                    console.log('status:' + data.statuscode);
                    $btnTxt.textContent = "Try again";
                    },2500);
                }
            })
            .catch(error => {
                // Handle any errors that occurred during the fetch
                console.error('Fetch error:', error);
            });
    
    }else{
        console.log("Not verified")
    }
}
function verifyukcp(params) {
    // "https://www.psychotherapy.org.uk/therapist/john-doe"
    const ukcpProfileUrl = document.getElementById('v-url-route-ukcp')
    let ukcpProfileLink = ukcpProfileUrl.value
    let errmsg = document.getElementById('err-ukcp')
    let spinner = document.getElementById('spinner')

    spinner.classList.remove("d-none")
    errmsg.className = "fade-out-err"
    errmsg.innerText = "";
    setTimeout(() => {
        //handle invalid url errors NB this will not capture false www mistakes eg wwmw
        if(!isValidUrl(ukcpProfileLink)) {
            console.log('invalid url');
            spinner.className = "d-none spinner-border spinner-border-sm"
            errmsg.className = "fade-in-err"
            errmsg.innerText = "Unable to verify - please add the url link to your UKCP profile. (If this keeps happening please contact us)";
            $verifyButton.className = "btn btn-warning"; 
        return
        }
    }, 2000);


    let ukcpUrlObj = new URL(ukcpProfileLink);
    
    if (ukcpUrlObj.href.includes("https://www.psychotherapy.org.uk/therapist/")) {
        console.log("Match go verify");
        params.append('verifyLink', ukcpProfileLink);
        // return true;
        // params.append(ukcpProfileUrl)
        console.log(params)
        fetch('/actions/fetch-profile/default/verify-ukcp', {
            method: 'POST',
            body: params,
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        })
            .then(response => {
                // Check if the request was successful
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                // Parse the response as JSON
                console.log(response.json);
                return response.json();
            })
            .then(data => {
                // Handle the JSON data
                console.log('datastatus:' + data);
                if(data.statuscode == "true"){
                    
                    setTimeout(() => {
                        confetti.reset();
                           // confetti
                    confetti({
                        particleCount: 100,
                        spread: 70
                    });
                    // do button verify message
                    $verifyButton.className = "btn btn-success";
                    $verifyButton.innerText = "Verified!";
                    $verifyButton.ariaDisabled;
                    $verifyButton.disabled = true;
                      }, 3000);
                } else{
                    //do error msg
                    setTimeout(() => {
                        vWarningContainer.classList.remove("d-none")
                        spinner.classList.add("d-none");
                        console.log('status:' + data.statuscode);
                        $btnTxt.textContent = "Try again";
                        },2500);
                    
                }
            })
            .catch(error => {
                // Handle any errors that occurred during the fetch
                console.error('Fetch error:', error);
            });
    
    }else{
        console.log("Not verified")
    }

    console.log('verify ukcp membership')
}
function verifyncps(data) {
    
    //https://www.search-ncps.com/search/FindaTherapist/NCS23-03863
    let regEx = /\b(https?:\/\/.*?\.[a-z]{2,4}\/[^\s]*\b)/g;
    let str ='https://www.search-ncps.com/search/FindaTherapist/'
    console.log('verify ncps membership')
    console.log(str.match(regEx));

}

function modifyText() {
    console.log(el.value)
    console.log(el)
}


function vverifybacp(data) {
    fetch(baseUrl + "/actions/fetch-profile/default/verify-bacp", {
        method: 'post',
        body: data,
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }
    }).then((response) => {
        return response.json()
    }).then((res) => {
        if (res.status === 201) {
            console.log("Post successfully created!")
        }
    }).catch((error) => {
        console.log(error)
    })

}

// Helper for fetching a CSRF token:
const getSessionInfo = function () {
    return fetch('/actions/users/session-info', {
        headers: {
            'Accept': 'application/json',
        },
    })
        .then(response => response.json());
};

function getCookie(name) {
  const m = document.cookie.match(new RegExp('(?:^|; )' + name.replace(/[-[\]{}()*+?.,\\^$|#\s]/g, '\\$&') + '=([^;]*)'));
  return m ? decodeURIComponent(m[1]) : null;
}
