addEventListener("trix-file-accept", event => {
    if (!event.file.type.startsWith("image/")) event.preventDefault()
    if (event.file.size > 10_000_000) event.preventDefault()
})

addEventListener("trix-attachment-add", event => {
    if (event.attachment.file) uploadFile(event.attachment)
})

addEventListener("trix-attachment-remove", event => {
    if (event.attachment) deleteAttachment(event.attachment)
})

function uploadFile(attachment) {
    let xhr = new XMLHttpRequest()

    const formData = new FormData()
    formData.append("Content-Type", attachment.file.type)
    formData.append("file", attachment.file)
    formData.append("name", attachment.file.name)

    xhr.open("POST", "/admin/attachments")

    xhr.upload.addEventListener("progress", event => {
        attachment.setUploadProgress(event.loaded / event.total * 100)
    })

    xhr.addEventListener("load", () => {
        if (xhr.status === 201) attachment.setAttributes({url: xhr.responseText, href: xhr.responseText})
    })

    xhr.send(formData)
}

function deleteAttachment(attachment) {
    let xhr = new XMLHttpRequest()
    xhr.open("DELETE", attachment.getAttribute("url"))
    xhr.send()
}
