import React, { useState } from 'react';
import ReactQuill from 'react-quill';
import 'react-quill/dist/quill.snow.css'; // import styles

const MyEditor = () => {
  const [value, setValue] = useState('');

  const handleChange = (content, delta, source, editor) => {
    setValue(content);
  };

  return (
    <div>
      <ReactQuill value={value} onChange={handleChange} />
    </div>
  );
};

export default MyEditor;
