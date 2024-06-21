import { Upload, Button, message } from 'antd';
import { UploadOutlined } from '@ant-design/icons';
import React, { useState } from 'react';

const ImageUpload = ({ value = [], onChange }) => {
  const [fileList, setFileList] = useState(value);

  const handleChange = info => {
    let newFileList = [...info.fileList];
    setFileList(newFileList);
    onChange(newFileList);
  };

  const handleRemove = file => {
    const newFileList = fileList.filter(item => item.uid !== file.uid);
    setFileList(newFileList);
    onChange(newFileList);
  };

  const beforeUpload = file => {
    const isJpgOrPng = file.type === 'image/jpeg' || file.type === 'image/png';
    if (!isJpgOrPng) {
      message.error('You can only upload JPG/PNG file!');
    }
    const isLt2M = file.size / 1024 / 1024 < 2;
    if (!isLt2M) {
      message.error('Image must smaller than 2MB!');
    }
    return isJpgOrPng && isLt2M;
  };

  return (
    <Upload
      listType="picture"
      fileList={fileList}
      onChange={handleChange}
      onRemove={handleRemove}
      beforeUpload={beforeUpload}
    >
      <Button icon={<UploadOutlined />}>Upload Avatar</Button>
    </Upload>
  );
};

export default ImageUpload;
