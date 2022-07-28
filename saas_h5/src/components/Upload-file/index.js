/**
 * Created by yaer on 2019/6/26;
 * @Email 740905172@qq.com
 * */

import PropsType from "prop-types";
import { ImagePicker } from "antd-mobile";
import { useState, useEffect } from "react";

const UploadFile = (props) => {
  const {
    maxFileLength, filesArr, fileAddFn, fileDeleteFn, isUpload,
  } = props;

  const [files, setFiles] = useState([]);

  useEffect(() => {
    setFiles(filesArr && filesArr || []);
  }, [filesArr]);
  return (

    <div onClick={imagePickerClick}>
      <ImagePicker
        onChange={fileAdd}
        disableDelete={!isUpload}
        files={files}
        selectable={isUpload && files.length < maxFileLength} />
    </div>
  );

  /**
   * 文件选中
   * @param file  选中文件 <array>
   * @param type  操作类型 delete/add
   * @param index 删除下标
   */
  function fileAdd(file, type, index) {
    let arr = [];
    if (type === "add") {
      arr = file;
      fileAddFn(arr.filter(item => item.file));
    } else {
      files.forEach((item) => {
        arr.push(item);
      });
      const [delete_data] = arr.splice(index, 1);

      // file存在证明为刚新增的文件，不能添加到删除文件数组中
      if (!delete_data.file) {
        fileDeleteFn([delete_data]);
      } else {
        fileAddFn(arr.filter(item => item.file));
      }
    }
    setFiles(arr);
  }

  function imagePickerClick() {
    window.htmlOnShow = "";
  }
};

UploadFile.propsType = {
  maxFileLength: PropsType.number.isRequired,
  filesArr: PropsType.array.isRequired,
  fileAddFn: PropsType.func.isRequired,
  isUpload: PropsType.bool,
  fileDeleteFn: PropsType.func.isRequired,
};

UploadFile.defaultProps = {
  isUpload: true,
};


export default UploadFile;
