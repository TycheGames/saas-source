package com.deepfinch.kyclib.model;

import java.io.Serializable;
import java.util.Arrays;

/**
 * Copyright (c) 2018-2019 DEEPFINCH Corporation. All rights reserved.
 */

public class DFKYCModel implements Serializable{
    private byte[] aadhaarImage;
    private String name;
    private String dob;
    private String gender;
    private String careOf;
    private String house;
    private String street;
    private String landMark;
    private String loc;
    private String po;
    private String subDist;
    private String dist;
    private String state;
    private String pc;

    public byte[] getAadhaarImage() {
        return aadhaarImage;
    }

    public void setAadhaarImage(byte[] aadhaarImage) {
        this.aadhaarImage = aadhaarImage;
    }

    public String getName() {
        return name;
    }

    public void setName(String name) {
        this.name = name;
    }

    public String getDob() {
        return dob;
    }

    public void setDob(String dob) {
        this.dob = dob;
    }

    public String getGender() {
        return gender;
    }

    public void setGender(String gender) {
        this.gender = gender;
    }

    public String getCareOf() {
        return careOf;
    }

    public void setCareOf(String careOf) {
        this.careOf = careOf;
    }

    public String getHouse() {
        return house;
    }

    public void setHouse(String house) {
        this.house = house;
    }

    public String getStreet() {
        return street;
    }

    public void setStreet(String street) {
        this.street = street;
    }

    public String getLandMark() {
        return landMark;
    }

    public void setLandMark(String landMark) {
        this.landMark = landMark;
    }

    public String getLoc() {
        return loc;
    }

    public void setLoc(String loc) {
        this.loc = loc;
    }

    public String getPo() {
        return po;
    }

    public void setPo(String po) {
        this.po = po;
    }

    public String getSubDist() {
        return subDist;
    }

    public void setSubDist(String subDist) {
        this.subDist = subDist;
    }

    public String getDist() {
        return dist;
    }

    public void setDist(String dist) {
        this.dist = dist;
    }

    public String getState() {
        return state;
    }

    public void setState(String state) {
        this.state = state;
    }

    public String getPc() {
        return pc;
    }

    public void setPc(String pc) {
        this.pc = pc;
    }

    @Override
    public String toString() {
        return "DFKYCModel{" +
                "aadhaarImage=" + Arrays.toString(aadhaarImage) +
                ", name='" + name + '\'' +
                ", dob='" + dob + '\'' +
                ", gender='" + gender + '\'' +
                ", careOf='" + careOf + '\'' +
                ", house='" + house + '\'' +
                ", street='" + street + '\'' +
                ", landMark='" + landMark + '\'' +
                ", loc='" + loc + '\'' +
                ", po='" + po + '\'' +
                ", subDist='" + subDist + '\'' +
                ", dist='" + dist + '\'' +
                ", state='" + state + '\'' +
                ", pc='" + pc + '\'' +
                '}';
    }
}
