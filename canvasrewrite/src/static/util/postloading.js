
async function findMarkedForPostLoading(inItem){
    let toProcess = inItem.querySelectorAll("[postload]");
    toProcess = Array.from(toProcess);
    
    toProcess = toProcess.map(element => processPostload(element, element.getAttribute("postload")));

    if(toProcess.length === 0) {
        document.dispatchEvent(new CustomEvent("PostloadingFinished"));
        return Promise.resolve(); 
    }

    return Promise.all(toProcess).then(() => {
        findMarkedForPostLoading(inItem); //Recursively process newly loaded content
    });
}

async function processPostload(replaceNode, url){
    // console.log("Postloading from " + url);
    try {
        const response = await fetch(url);
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        const html = await response.text();
        const tempDiv = document.createElement(replaceNode.parentNode.tagName);
        tempDiv.innerHTML = html;
        replaceNode.replaceWith(...tempDiv.childNodes);
    } catch (error) {
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = "<div style='color:red;'>Error loading content.</div>";
        replaceNode.replaceWith(tempDiv);
        console.error('There has been a problem with your fetch operation:', error);
    }
}

document.addEventListener("DOMContentLoaded", () => {
    findMarkedForPostLoading(document);
});