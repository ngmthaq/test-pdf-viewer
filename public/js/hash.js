// console.log(Crypto);

const key = "6268890F-9B58-484C-8CDC-34F9C6A9";
const iv = "6268890F-9B58-48";

const cipher = CryptoJS.AES.encrypt("Apple", CryptoJS.enc.Utf8.parse(key), {
  iv: CryptoJS.enc.Utf8.parse(iv),
  mode: CryptoJS.mode.CBC,
});

const hash = cipher.toString();

console.log(hash, hash.toString());

const plain = CryptoJS.AES.decrypt(hash, CryptoJS.enc.Utf8.parse(key), {
  iv: CryptoJS.enc.Utf8.parse(iv),
  mode: CryptoJS.mode.CBC,
});

console.log(plain, plain.toString(CryptoJS.enc.Utf8));
