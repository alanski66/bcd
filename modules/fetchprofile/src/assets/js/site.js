console.log('module js');



const $button = document.getElementById('verify');
const memberOrg = document.getElementById('memberOrg');
console.log(memberOrg)
const isValidUrl = (urlString) => {
    var urlPattern = new RegExp('^(https?:\\/\\/)?' + // validate protocol
        '((([a-z\\d]([a-z\\d-]*[a-z\\d])*)\\.)+[a-z]{2,}|' + // validate domain name
        '((\\d{1,3}\\.){3}\\d{1,3}))' + // validate OR ip (v4) address
        '(\\:\\d+)?(\\/[-a-z\\d%_.~+]*)*' + // validate port and path
        '(\\?[;&a-z\\d%_.~+=-]*)?' + // validate query string
        '(\\#[-a-z\\d_]*)?$', 'i'); // validate fragment locator
    return !!urlPattern.test(urlString);
}
$button.addEventListener('click', function (e) {
    const params = new FormData();
    // console.log(isValidUrl(verifyLink.value));
    console.log("jj" + memberOrg[0].value)
    // params.append('verifyLink', verifyLink.value);
    // params.append('userId', $button.dataset.userId);
    params.append($button.dataset.csrfTokenName, $button.dataset.csrfTokenValue);
    //params.append('fullName', prompt('New name:'));

    if (memberOrg.value === "bacp") verifybacp() // format https://www.bacp.co.uk/therapists/386443
    if (memberOrg.value === "ukcp") verifyukcp()
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
function verifyukcp(data) {
    console.log('verify ukcp membership')
}
function verifyncps(data) {
    console.log('verify ncps membership')
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
