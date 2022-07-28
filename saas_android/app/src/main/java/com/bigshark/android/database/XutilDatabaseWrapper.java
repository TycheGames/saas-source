package com.bigshark.android.database;

import com.socks.library.KLog;

import org.xutils.DbManager;
import org.xutils.db.DbManagerImpl;
import org.xutils.db.sqlite.SqlInfo;
import org.xutils.db.sqlite.WhereBuilder;
import org.xutils.ex.DbException;

import java.util.Collections;
import java.util.List;

/**
 * Created by Administrator on 2017/3/13.
 */
public class XutilDatabaseWrapper {

    private DbManager dbManager;

    public XutilDatabaseWrapper() {
        try {
            this.dbManager = DbManagerImpl.getInstance(
                    new DbManagerImpl.DaoConfig()
                            .setDbName("services_info_db")
                            .setDbVersion(22)
//                            .setDbOpenListener(new DbManager.DbOpenListener() {
//                                @Override
//                                public void onDbOpened(DbManager db) {
//                                    // 开启WAL, 对写入加速提升巨大
//                                    db.getDatabase().enableWriteAheadLogging();
//                                }
//                            })
                            .setDbUpgradeListener(new DbManager.DbUpgradeListener() {
                                @Override
                                public void onUpgrade(DbManager db, int oldVersion, int newVersion) throws DbException {
                                    KLog.d("old ver:" + oldVersion + ", new ver:" + newVersion);
                                }
                            }));
        } catch (DbException e) {
            e.printStackTrace();
        }
    }

    public void save(Object entity) {
        try {
            dbManager.save(entity);
        } catch (DbException e) {
            e.printStackTrace();
        }
    }


    public void delete(Object entity) {
        try {
            dbManager.delete(entity);
        } catch (DbException e) {
            e.printStackTrace();
        }
    }


    /**
     * 更新数据 （主键ID必须不能为空）
     *
     * @param entity
     */
    public void update(Object entity) {
        try {
            dbManager.update(entity);
        } catch (DbException e) {
            e.printStackTrace();
        }
    }

    /**
     * 根据条件更新数据
     *
     * @param strWhere 条件为空的时候，将会更新所有的数据
     */
    public void update(Object entity, String strWhere) {
        try {
            dbManager.update(entity, strWhere);
        } catch (DbException e) {
            e.printStackTrace();
        }
    }

    /**
     * 查找所有的数据
     */
    public <T> List<T> findAll(Class<T> clazz) {
        List<T> results = null;
        try {
            results = dbManager.findAll(clazz);
        } catch (DbException e) {
            e.printStackTrace();
        }
        return results == null ? Collections.<T>emptyList() : results;
    }

    /**
     * 根据条件查找所有数据
     */
    public <T> List<T> findAllByWhere(Class<T> clazz, WhereBuilder whereBuilder) {
        List<T> results = null;
        try {
            results = dbManager.selector(clazz).where(whereBuilder).findAll();
        } catch (DbException e) {
            e.printStackTrace();
        }
        return results == null ? Collections.<T>emptyList() : results;
    }


}
