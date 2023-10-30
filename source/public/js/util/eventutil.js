"use strict"

;class EventUtil {

    static removeEventListener = (event, element, callback) => {
        return new Promise((resolve, reject) => {
            element.removeEventListener(event, callback)
            resolve()
        })
    }

}
