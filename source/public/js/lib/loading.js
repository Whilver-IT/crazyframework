"use strict"

;class Loading {

    static #wheelPreventDefault = (e) => {e.preventDefault()}

    constructor(){
        throw "Uncaught Error: Call to Loading.constructor()"
    }

    static #loadImage(src){
        return new Promise((resolve, reject) => {
            const img = new Image()
            img.onload = () => resolve(img)
            img.onerror = (e) => reject(e)
            img.src = src
        })
    }

    static start(bgColor, imgFile, imgWidth, imgHeight, alt){

        let body = document.getElementsByTagName("body")
        if(body && body.length){

            this.#loadImage(imgFile).then((data) => {
                try {

                    document.addEventListener("touchmove", this.#wheelPreventDefault, {passive: false})
                    document.addEventListener("wheel", this.#wheelPreventDefault, {passive: false})

                    let divImage = document.createElement("div")
                    divImage.style.margin = "0px auto"
                    divImage.style.width = imgWidth + "px"
                    divImage.style.height = imgHeight + "px"
                    divImage.style.zIndex = "110"

                    let img = document.createElement("img")
                    img.src = imgFile
                    img.alt = (typeof alt == "undefined" || !alt.length) ? "loading" : alt

                    let divLoadingInner = document.createElement("div")
                    divLoadingInner.style.textAlign = "center"
                    divLoadingInner.style.verticalAlign = "middle"
                    divLoadingInner.style.display = "table-cell"
                    divLoadingInner.style.backgroundColor = "rgba(200, 200, 200, 0.3)"

                    let divLoading = document.createElement("div")
                    divLoading.className = "loading"
                    divLoading.style.top = "0px"
                    divLoading.style.left = "0px"
                    divLoading.style.display = "table"
                    divLoading.style.width = "100%"
                    divLoading.style.height = "100%"
                    divLoading.style.zIndex = "100"
                    divLoading.style.position = "fixed"
                    divLoading.style.margin = "0"

                    divImage.appendChild(img)
                    divLoadingInner.appendChild(divImage)
                    divLoading.appendChild(divLoadingInner)
                    body[0].append(divLoading)
                } catch(e){
                    console.log(e)
                }
            }).catch((le) => {
                console.log(le)
            })
        }
    }

    static stop(){
        [...document.getElementsByClassName("loading")].forEach(loading => loading.remove())
        let html = document.getElementsByTagName("html")
        if(html && html.length){
            html[0].style.height = null
        }
        let body = document.getElementsByTagName("body")
        if(body && body.length){
            body[0].style.height = null
        }
        document.removeEventListener("touchmove", this.#wheelPreventDefault)
        document.removeEventListener("wheel", this.#wheelPreventDefault)
    }
}
