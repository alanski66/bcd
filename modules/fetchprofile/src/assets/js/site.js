console.log('module js');



const $verifyButton = document.getElementById('verify');
const memberOrg = document.getElementById('memberOrg');
const idRouteContainer = document.getElementById('id-route-container');
const urlRouteContainerUKCP = document.getElementById('url-route-container-ukcp');
const urlRouteContainerOther = document.getElementById('url-route-container-other');

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
    // console.log(isValidUrl(verifyLink.value));
    console.log("jj" + memberOrg[0].value)
    // params.append('verifyLink', verifyLink.value);
    // params.append('userId', $button.dataset.userId);
    params.append($verifyButton.dataset.csrfTokenName, $verifyButton.dataset.csrfTokenValue);
    //params.append('fullName', prompt('New name:'));

    if (memberOrg.value === "bacp") verifybacp() // format https://www.bacp.co.uk/therapists/386443
    if (memberOrg.value === "ukcp") verifyukcp(params) // https://www.psychotherapy.org.uk/therapist/Maja-Andersen-JJYWLQA5
    if (memberOrg.value === "ncps") verifyncps() //format https://www.search-ncps.com/search/FindaTherapist/NCS14-01401
    return
    //add radio detect and redirect based on

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
            console.log('dd' + data.statuscode);
            if(data.statuscode == "true"){
                // do button verify message
                $button.className = "btn btn-success";
                $button.innerText = "You're verified";
                confetti({
                    particleCount: 100,
                    spread: 70,
                    origin: { x: 0.2, y: 0.6 }
                });
                setTimeout(() => {
                    confetti.reset();
                  }, 3000);
            } else{
                //do error msg
            }
        })
        .catch(error => {
            // Handle any errors that occurred during the fetch
            console.error('Fetch error:', error);
        });
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

function verifybacp(data) {
    console.log('verify bacp membership')
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
  
        //handle invalid url errors
        if(!isValidUrl(ukcpProfileLink)) {
            spinner.className = "d-none spinner-border spinner-border-sm"
            errmsg.className = "fade-in-err"
            errmsg.innerText = "Unable to verify - please add the url link to your UKCP profile. (If this keeps happening please contact us)";
            $verifyButton.className = "btn btn-warning"; 
        //     $verifyButton.innerText = "uh-oh!";
        //     setTimeout(() => {
        //         $verifyButton.className = "btn btn-primary"; 
        //         $verifyButton.innerText = "Verify";
        //         spinner.className = "d-none ski"
        //       }, 3000);
        return
        }
    }, 2000);


    let ukcpUrlObj = new URL(ukcpProfileLink);
    
    if (ukcpUrlObj.href.includes("https://www.psychotherapy.org.uk/therapist/")) {
        console.log("Match go verify");
        params.append('verifyLink', ukcpProfileLink);
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
                    $verifyButton.className = "btn btn-success";
                    $verifyButton.innerText = "Verified!";
                    $verifyButton.ariaDisabled;
                    $verifyButton.disabled = true;
                      }, 3000);
                } else{
                    //do error msg
                    console.log('status:' + data.statuscode);
                    $verifyButton.innerText = "Unable to verify";
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
