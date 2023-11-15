// https://www.tiktok.com/@juno_okyo/video/7297270767052426501?_r=1&_t=8h3ZwMZcXPl
// ddt = disable dev-tool (f12)
// dt = dev-tool
function onDtOpen() {
	console.log(Math.random());
}
class DtChecker extends Error {
	toString() {}
	get message() {
		onDtOpen();
	}
}
// console.log(new DtChecker());
