
function findMarkedForPostLoading(inItem){
    let toProcess = inItem.querySelectorAll("[postload]")
        .forEach(element => processPostload(element, element.getAttribute("postload")));
    if(toProcess.length === 0) return;
    Promise.all(toProcess).then(() => {
        findMarkedForPostLoading(inItem); //Recursively process newly loaded content
    });
}

async function processPostload(replaceNode, url){
    try {
        const response = await fetch(url);
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        const html = await response.text();
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = html;
        replaceNode.replaceWith(...tempDiv.childNodes);
    } catch (error) {
        console.error('There has been a problem with your fetch operation:', error);
    }
}