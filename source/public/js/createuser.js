"use strict"

;document.addEventListener("DOMContentLoaded", (dcl) => {
	const search_zip = document.getElementById("searchzip")
	const search_zip_click = (e) => {
		EventUtil.removeEventListener("click", search_zip, search_zip_click).then(() => {
			const param = {
				"method": "get",
				"url": "/ajax/searchzip",
				"params": {"zip": encodeURIComponent(document.getElementById("zipcode").value)},
			}
			axios(param).then((res) => {
				if(res.data && res.data.length){
					document.getElementById("address").value = res.data[0]['name'] + res.data[0]['suffix'] + res.data[0]['city'] + res.data[0]['street']
				}
			}).catch((err) => {
				console.log(err)
			}).finally(() => {
				search_zip.addEventListener("click", search_zip_click)
			})
		})
	}
	search_zip.addEventListener("click", search_zip_click)

	const send_regist = document.getElementById("regist")
	const send_regist_click = (e) => {
		EventUtil.removeEventListener("click", send_regist, send_regist_click).then(() => {
			document.querySelectorAll(".err").forEach((elem) => {
				elem.remove()
			})
			const param = {
				"method": "post",
				"url": "/ajax/createuser",
				"data": HttpUtil.serializePost("form input,select"),
			}
			axios(param).then((res) => {
				const err_keys = Object.keys(res.data.err_msg)
				if(err_keys.length){
					err_keys.forEach((key) => {
						const target_element = document.getElementById(key)
						if(target_element){
							const err_element = document.createElement("div")
							err_element.id = key
							err_element.innerHTML = res.data.err_msg[key]
							err_element.className = "err"
							target_element.parentNode.append(err_element)
						}
					})
				} else {
					location.href = "/createuser/finish"
				}
			}).catch((err) => {
				console.log(err)
			}).finally(() => {
				send_regist.addEventListener("click", send_regist_click)
			})
		})
	}
	send_regist.addEventListener("click", send_regist_click)
})