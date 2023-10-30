"use strict"

;document.addEventListener("DOMContentLoaded", (dcl) => {
    const send_login = document.getElementById("login")
    const send_login_click = (e) => {
        EventUtil.removeEventListener("click", send_login, send_login_click).then(() => {
            const err_element = document.querySelector(".err")
            if(err_element){
                err_element.remove()
            }
            const param = {
                "method": "post",
                "url": "/ajax/login",
                "data": HttpUtil.serializePost("form input"),
            }
            axios(param).then((res) => {
                if(res.data){
                    if(res.data.is_login){
                        location.href = "/menu"
                    }
                    if(!res.data.exec_login || !res.data.is_login){
                        const err_element = document.createElement("div")
                        err_element.className = "err"
                        document.getElementById("password").parentNode.append(err_element)
                        err_element.innerHTML = "IDまたはパスワードに誤りがあります"
                    }
                    document.getElementsByName("_csrf")[0].value = res.data._csrf
                }
            }).catch((err) => {
                console.log(err)
            }).finally(() => {
                send_login.addEventListener("click", send_login_click)
            })
        })
    }
    send_login.addEventListener("click", send_login_click)
})