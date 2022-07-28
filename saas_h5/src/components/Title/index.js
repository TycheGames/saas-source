/**
 * Created by yaer on 2019/7/5;
 * @Email 740905172@qq.com
 * */
import PropTypes from "prop-types";
import "./index.less";

const Title = (props) => {
  const { title } = props;
  return (
    <div className="title-wrapper">
      <h1>{title}</h1>
    </div>
  );
};

Title.propTypes = {
  title: PropTypes.string.isRequired,
};

export default Title;
