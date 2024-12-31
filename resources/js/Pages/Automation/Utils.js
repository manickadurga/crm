function toJSON(elements, fileName = 'WorkFlow') {
  const downloadLink = document.createElement("a");
  const fileBlob = new Blob([JSON.stringify(elements, null, 2)], {
    type: "application/json",
  });
  downloadLink.href = URL.createObjectURL(fileBlob);
  downloadLink.download = `${fileName}.json`;  // Use the dynamic file name
  downloadLink.click();
}

export { toJSON };
