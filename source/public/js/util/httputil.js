"use strict"

;class HttpUtil {

    static serialize = (tagSelector) => {
        return this.#serializeMain(tagSelector, "get")
    }

    static serializePost = (tagSelector) => {
        return this.#serializeMain(tagSelector, "post")
    }

    static #serializeMain = (tagSelector, method) => {
        const tagElements = document.querySelectorAll(tagSelector)
        const data = new URLSearchParams()
        for(let i = 0; i < tagElements.length; i++){
            if(tagElements[i].name){
                const value = tagElements[i].value
                data.append(tagElements[i].name, method == "get" ? encodeURIComponent(value) : value)
            }
        }
        return data
    }
}
